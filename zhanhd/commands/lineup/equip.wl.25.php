<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Swoole\Client,
    System\Stdlib\Object;

/**
 *
 */
use Zhanhd\ReqRes\Lineup\Equip          as Request,
    Zhanhd\ReqRes\Lineup\EquipResponse  as Response,
    Zhanhd\Object\Player\Entity         as PlayerEntity,
    Zhanhd\Object\Player\Lineup         as PlayerLineup,
    Zhanhd\Object\Player\Crusade        as PlayerCrusade,
    Zhanhd\Extension\Achievement\Module as AchievementModule,
    Zhanhd\Extension\Guide              as GuideModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;

    $gid = $request->gid->intval();
    $pos = $request->pos->intval();
    $use = $request->use->intval();

    if ($gid < 1 || $gid > 4) {
        return $c->addReply($this->errorResponse->error('invalid lineup'));
    }

    $pl = $p->getLineup($gid);
    // not crusade
    foreach ($p->getCrusades() as $pc) {
        if ($pc->flags <> PlayerCrusade::FLAG_DONE && $pc->gid == $pl->gid) {
            return $c->addReply($this->errorResponse->error('lineup in crusade'));
        }
    }

    // reindex heros by pos
    $pl->heros = $pl->heros->map(null, function($i, $o) {
        return $o->pos;
    });

    if (null === ($plh = $pl->heros->get($pos))) {
        return $c->addReply($this->errorResponse->error('invalid position'));
    } else if ($plh->peid == 0) {
        return $c->addReply($this->errorResponse->error('embed hero first'));
    }

    $ope = new PlayerEntity;
    if ($plh->equips->$use) {
        $ope->findByPid($plh->equips->$use, $p->id);
    }

    if ($ope->id == $request->pe->peid->intval()) {
        goto sendResponse;
    }

    $npe = new PlayerEntity;
    if ($request->pe->peid->intval()) {
        if (false === $npe->findByPid($request->pe->peid->intval(), $p->id)) {
            return $c->addReply($this->errorResponse->error('pe not found'));
        } else if ($use <> $npe->e->type) {
            return $c->addReply($this->errorResponse->error('pe not usable'));
        } else if ($npe->flags == PlayerEntity::FLAG_INUSE) {
            return $c->addReply($this->errorResponse->error('pe already inuse'));
        } else if ($npe->e->lvlreq > $plh->pe->lvl) {
            return $c->addReply($this->errorResponse->error('hero level not enough'));
        } else if (false === isset($npe->e->rules[$plh->pe->a->type])) {
            return $c->addReply($this->errorResponse->error('use-rule denied'));
        }
    }

    // update equipment
    $plh->equips->$use = $npe->id;
    $plh->equips->save();

    // inuse pe
    if ($npe->id) {
        $npe->flags = PlayerEntity::FLAG_INUSE;
        $npe->gid = $gid;
        $npe->save();
    }

    // unuse pe
    if ($ope->id) {
        $ope->flags = PlayerEntity::FLAG_UNUSE;
        $ope->save();
    }

    // trigger achievement event
    (new AchievementModule($p))->trigger((new Object)->import(array(
        'cmd'    => 'lineup',
        'strval' => 'equip',
    )));

    /* update player-power-rank */

    // sending equip response
    sendResponse: {
        $r = new Response;
        $r->retval->resize(1);
        $ret = $r->retval->get(0);
        $ret->gid->intval($gid);
        $ret->pos->intval($pos);
        $ret->use->intval($use);

        if (isset($npe)) {
            $ret->pe->peid->intval($npe->id);
            $ret->pe->eid ->intval($npe->eid);
        } else if (isset($ope)) {
            $ret->pe->peid->intval($ope->id);
            $ret->pe->eid ->intval($ope->eid);
        }

        $c->addReply($r);
    }
    GuideModule::aspect($c, 9);
};

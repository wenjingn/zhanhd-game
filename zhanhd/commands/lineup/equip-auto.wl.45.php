<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Stdlib\Object,
    System\Swoole\Client;

/**
 *
 */
use Zhanhd\ReqRes\Lineup\EquipAuto          as Request,
    Zhanhd\ReqRes\Lineup\EquipAutoResponse  as Response,
    Zhanhd\Config\Entity                    as SourceEntity,
    Zhanhd\Object\Player\Lineup             as PlayerLineup,
    Zhanhd\Object\Player\Entity             as PlayerEntity,
    Zhanhd\Object\Player\Crusade            as PlayerCrusade,
    Zhanhd\Extension\Achievement\Module     as AchievementModule,
    Zhanhd\Extension\Guide                  as GuideModule;

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

    if (null === ($pl = $p->getLineups('gid')->get($gid))) {
        /* todo: new group? */
        return $c->addReply($this->errorResponse->error('invalid lineup'));
    }

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

    // weapon, armor, horse, jewel
    $npes = new Object;
    $opes = $plh->getEquipEntities();

    // filter available pes
    foreach ($p->getEntities() as $pe) {
        if ($pe->flags == PlayerEntity::FLAG_INUSE) {
            continue;
        } else if ($pe->e->type == SourceEntity::TYPE_HERO) {
            continue;
        } else if ($pe->e->lvlreq > $plh->pe->lvl || false === isset($pe->e->rules[$plh->pe->a->type])) {
            continue;
        } else if (($ope = $opes->get($pe->e->type)) && ($ope->e->rarity > $pe->e->rarity || $ope->eid >= $pe->eid)) {
            continue;
        }

        if (null === ($npe = $npes->get($pe->e->type))) {
            $npes->set($pe->e->type, $pe);
        } else if ($npe->e->rarity < $pe->e->rarity || $npe->eid < $pe->eid) {
            $npes->set($pe->e->type, $pe);
        }
    }

    // final use pes
    $pes = new Object;

    // unuse old pes
    foreach ($opes as $use => $ope) {
        if (isset($npes->$use)) {
            $ope->flags = PlayerEntity::FLAG_UNUSE;
            $ope->save();
        } else {
            $pes->set(null, $ope);
        }
    }

    // inuse new pes
    foreach ($npes as $use => $npe) {
        $plh->equips->$use = $npe->id;
        $plh->equips->save();

        $npe->flags = PlayerEntity::FLAG_INUSE;
        $npe->gid = $gid;
        $npe->save();

        $pes->set(null, $npe);
    }

    if ($npes->count()) {
        // trigger achievement event
        (new AchievementModule($p))->trigger((new Object)->import(array(
            'cmd'    => 'lineup',
            'strval' => 'equip',
        )));
    }

    /* update player-power-rank */

    // add response
    $r = new Response;
    $r->retval->resize($pes->count());

    foreach ($pes as $i => $npe) {
        $r->retval->get($i)->gid->intval($gid);
        $r->retval->get($i)->pos->intval($pos);

        $r->retval->get($i)->use->intval($npe->e->type);

        $r->retval->get($i)->pe->peid->intval($npe->id);
        $r->retval->get($i)->pe->eid ->intval($npe->eid);
    }

    $c->addReply($r);

    GuideModule::aspect($c, 9);
};

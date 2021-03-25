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
use Zhanhd\ReqRes\Lineup\Hero\Request,
    Zhanhd\ReqRes\Lineup\Hero\Response,
    Zhanhd\ReqRes\Lineup\EquipResponse,
    Zhanhd\Config\Store,
    Zhanhd\Config\Entity                as SourceEntity,
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

    $fid = $request->fid->intval();
    if (null === ($f = Store::get('formation', $fid))) {
        return $c->addReply($this->errorResponse->error('invalid formation'));
    }

    $gid = $request->gid->intval();
    if ($gid < 1 || $gid > 4) {
        return $c->addReply($this->errorResponse->error('invalid lineup'));
    }
    if ($request->lineups->size() != 6) {
        return $c->addReply($this->errorResponse->error('invalid argvs'));
    }

    $pl = $p->getLineup($gid);
    // not crusade
    foreach ($p->getCrusades() as $pc) {
        if ($pc->flags <> PlayerCrusade::FLAG_DONE && $pc->gid == $pl->gid) {
            return $c->addReply($this->errorResponse->error('lineup in crusade'));
        }
    }

    // variables for validating/updating pes
    $nlineup = $olineup = $peInuse = $peUnuse = $eInuse = [];

    // reindex heros by pos
    $pl->heros = $pl->heros->map(null, function($i, $o) {
        return $o->pos;
    });

    // unuse previous heros
    foreach ($pl->heros as $plh) {
        if ($plh->peid) {
            $peUnuse[$plh->peid] = $plh->pe;
            $olineup[$plh->pos] = $plh->peid;
        }
    }

    $costsum = 0;
    $intsum  = 0;
    $lineups = [];
    foreach ($request->lineups as $lineup) {
        $lineups[$lineup->pos->intval()] = $lineup->pe->peid->intval();
    }
    foreach ($lineups as $pos => $peid) {
        if (false === array_key_exists($pos, PlayerLineup::$positions)) {
            return $c->addReply($this->errorResponse->error('invalid position'));
        }

        $pe = new PlayerEntity;
        if ($peid) {
            if (isset($peInuse[$peid])) {
                return $c->addReply($this->errorResponse->error('using duplicate entity'));
            }

            if (false === $pe->findByPid($peid, $p->id)) {
                return $c->addReply($this->errorResponse->error('pe not found'));
            }

            if ($pe->e->type <> SourceEntity::TYPE_HERO) {
                return $c->addReply($this->errorResponse->error('invalid pe type'));
            }

            if (isset($peUnuse[$peid])) {
                unset($peUnuse[$peid]);
            } else if ($pe->flags == PlayerEntity::FLAG_INUSE) {
                return $c->addReply($this->errorResponse->error('hero already inuse'));
            }

            if (isset($eInuse[$pe->eid])) {
                return $c->addReply($this->errorResponse->error('using duplicate entity'));
            }

            $peInuse[$peid]    = $pe;
            $eInuse [$pe->eid] = $pe->e;
            $nlineup[$pos]     = $pe->id;

            // cost & int
            $costsum += $pe->e->cost;
            $intsum  += (integer)($pe->property->int / 100);
        } else if ($pos == PlayerLineup::CAPTAIN_POSITION) {
            return $c->addReply($this->errorResponse->error('captain cannot be empty'));
        }

        $pl->heros->$pos->peid = $pe->id;
        $pl->heros->$pos->pe = $pe;
    }

    /* validate intsum */
    if ($intsum < $f->intreq) {
        return $c->addReply($this->errorResponse->error('int not enough'));
    }

    // update flags for unused pes
    foreach ($peUnuse as $pe) {
        $pe->flags = PlayerEntity::FLAG_UNUSE;
        $pe->save();
    }

    // update flags for inused pes
    foreach ($peInuse as $pe) {
        $pe->flags = PlayerEntity::FLAG_INUSE;
        $pe->gid   = $gid;
        $pe->save();
    }

    /* check equip */
    $unequip = [];
    foreach ($olineup as $pos => $peid) {
        if (false === isset($nlineup[$pos])) {
            $plh = $pl->heros->$pos;
            $equips = $plh->getEquipEntities();
            foreach ($equips as $part => $equip) {
                $plh->equips->$part = 0;
                $equip->flags = PlayerEntity::FLAG_UNUSE;
                $equip->save();
                $unequip[$pos][] = $equip;
            }
            $plh->equips->save();
        } else if ($nlineup[$pos] != $peid) {
            $plh = $pl->heros->$pos;
            $equips = $plh->getEquipEntities();
            foreach ($equips as $part => $equip) {
                if ($equip->e->isSuitable($pl->heros->$pos->pe)) continue;
                $plh->equips->$part = 0;
                $equip->flags = PlayerEntity::FLAG_UNUSE;
                $equip->save();
                $unequip[$pos][] = $equip;
            }
            $plh->equips->save();
        }
    }

    $r = new EquipResponse;
    $i = 0;
    foreach ($unequip as $pos => $equips) {
        $r->retval->append(count($equips));
        foreach ($equips as $equip) {
            $o = $r->retval->get($i);
            $o->gid->intval($pl->gid);
            $o->pos->intval($pos);
            $o->use->intval($equip->e->type);
            $i++;
        }
    }
    $c->addReply($r);

    // trigger achievement event
    (new AchievementModule($p))->trigger((new Object)->import(array(
        'cmd'    => 'lineup',
        'strval' => 'hero',
    )));

    /* update player-power-rank */

    // update fid for pl
    if ($pl->fid <> $f->id) {
        $pl->fid = $f->id;

        // trigger achievement event
        (new AchievementModule($p))->trigger((new Object)->import(array(
            'cmd'    => 'lineup',
            'strval' => 'formation',
        )));
    }
    $pl->save();

    // sending lineup response
    $r = new Response;
    $r->retval->fromObject($pl);
    $c->addReply($r);

    if ($p->profile->guideId < 1) {
        GuideModule::aspect($c, 1);
    } else {
        GuideModule::aspect($c, 6);
    }
};

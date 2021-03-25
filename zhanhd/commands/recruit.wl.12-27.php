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
use Zhanhd\ReqRes\RecruitRequest            as Request,
    Zhanhd\ReqRes\Building\ResourceResponse as BuildingResourceResponse,
    Zhanhd\ReqRes\Recruit\HeroResponse,
    Zhanhd\ReqRes\Recruit\PropResponse,
    Zhanhd\ReqRes\Recruit\EquipResponse,
    Zhanhd\ReqRes\SysMsgResponse,
    Zhanhd\Config\Store,
    Zhanhd\Config\Entity                    as SourceEntity,
    Zhanhd\Config\NewzoneMission,
    Zhanhd\Config\WeekMission,
    Zhanhd\Extension\Achievement\Module     as AchievementModule,
    Zhanhd\Extension\NewzoneMission\Module  as NewzoneMissionModule,
    Zhanhd\Extension\WeekMission\Module     as WeekMissionModule,
    Zhanhd\ReqRes\PropUse\PropRemainResponse,
    Zhanhd\Extension\Service\Module         as ServiceModule,
    Zhanhd\Extension\TaskEmitter,
    Zhanhd\Extension\Guide                  as GuideModule;

/**
 *
 */
$CountGoods = [
    2030, //钻石单抽
    2031, //钻石十连抽
];

/**
 *
 */
return function(Client $c) use ($CountGoods) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;


    if (null === ($g = Store::get('goods', $request->gid->intval())) || $g->enable == 0) {
        return $c->addReply($this->errorResponse->error('notfound goods'));
    }

    // goods 2030 is special
    $free = false;
    if ($g->id == 2030) {
        if ($this->ustime - $p->recent->freeRecruit2030 > 48 * 3600 * 1000000) {
            $free = true;
        }
    }

    // requirements
    $propUsed = [];
    if ($g->requirement && $free === false) {
        foreach ($g->requirement as $eid => $num) {
            if ($p->profile->$eid < $num) {
                return $c->addReply($this->errorResponse->error('resource not enough'));
            }
        }

        foreach ($g->requirement as $eid => $num) {
            $e = Store::get('entity', $eid);
            if ($e->isProp()) {
                $propUsed[$eid] = true;
            }
            $p->profile->$eid -= $num;

            // logger
            $c->local->logger->set(null, array(
                'eid' => $eid,
                'cnt' => $num,
            ));
        }
    }

    // gold
    if ($g->gold && $free === false) {
        if ($g->incr) {
            $ckey = $g->getCounterKey();
            $gold = $g->gold + $g->incr * $p->counterCycle->$ckey;
            $p->counterCycle->$ckey++;
        } else {
            $gold = $g->gold;
        }

        if ($g->id == 2031 && $p->counterCycle->{$g->getCounterKey()} < 1) {
            $gold = (int)($gold/2);
        }

        if ($p->gold < $gold) {
            return $c->addReply($this->errorResponse->error('notenough diamond'));
        }

        $p->decrGold($gold);
    }

    if ($this->currcmd->code == 27) {
        $p->counterCycle->equipRecruit++;
        $p->counter->equipRecruit++;

        NewzoneMissionModule::trigger($p, $this, NewzoneMission::TYPE_RECRUITEQUIP, $p->counter->equipRecruit);
    }

    if (in_array($g->id, $CountGoods)) {
        /* ($g->incr > 0) has counted yet */
        if ($g->incr == 0) {
            $ckey = $g->getCounterKey();
            $p->counterCycle->$ckey++;
            $p->counter->$ckey++;
        }
    }

    if (!empty($propUsed)) {
        foreach ($propUsed as $propId => $true) {
            $propRemainResponse = new PropRemainResponse;
            $propRemainResponse->propId->intval($propId);
            $propRemainResponse->num->intval($p->profile->$propId);
            $c->addReply($propRemainResponse);
        }
    }

    // pick & increate entity
    $increases = (new Object)->import([
        'h' => [],
        'e' => [],
    ]);

    /**
     * goods 2031 保底最少一个4星武将
     */
    $picked = Store::get('edrop', $g->epid)->pick();
    if ($g->id == 2031) {
        $guarantee = true;
        foreach ($picked as $eid => $o) {
            if ($o->e->rarity >= 4) {
                $guarantee = false;
            }
        }
        if ($guarantee) {
            if ($o->n > 1) {
                $o->n--;
            } else {
                unset($picked->$eid);
            }

            /* 302004 四星武将随机 */
            $eid = Store::get('egroup', 302004)->pickone();
            $picked->set($eid, [
                'e' => Store::get('entity', $eid),
                'n' => 1,
            ]);
        }
        $p->counterWeekly->diarec+=10;
        $p->counterWeekly->save();
        WeekMissionModule::trigger($p, $this, WeekMission::TYPE_DIAREC, $p->counterWeekly->diarec);
    } else if ($g->id == 2030) {
        $p->counterWeekly->diarec++;
        $p->counterWeekly->save();
        WeekMissionModule::trigger($p, $this, WeekMission::TYPE_DIAREC, $p->counterWeekly->diarec);
    }

    $p->increaseEntity($picked, function($pe, $c) use ($increases) {
        switch ($pe->e->type) {
        case SourceEntity::TYPE_HERO:
            $increases->h->set(null, $pe);

            // trigger achievement event
            (new AchievementModule($c->local->player))->trigger((new Object)->import(array(
                'cmd'    => 'recruit',
                'strval' => 'hero',
            )));

            break;

        case SourceEntity::TYPE_WEAPON:
        case SourceEntity::TYPE_ARMOR:
        case SourceEntity::TYPE_HORSE:
        case SourceEntity::TYPE_JEWEL:
        case SourceEntity::TYPE_RING:
            $increases->e->set(null, $pe);

            // trigger achievement event
            (new AchievementModule($c->local->player))->trigger((new Object)->import(array(
                'cmd'    => 'recruit',
                'strval' => 'equip',
            )));

            break;
        }

        // logger
        $c->local->logger->set(null, array(
            'eid'  => $pe->e->id,
            'cnt'  => 1,
            'peid' => $pe->id,
        ));
    }, $c);

    foreach ($increases as $type => $pes) {
        if (($count = $pes->count()) < 1) {
            continue;
        }

        switch ($type) {
        case 'h':
            $r = new HeroResponse;
            $r->retval->resize($count);
            foreach ($r->retval as $i => $o) {
                $o->fromPlayerEntityObject($pes->get($i), $this);
            }
            if ($free) {
                $p->recent->freeRecruit2030 = $this->ustime;
            }
            $cd = 86400000000 * 2 + $p->recent->freeRecruit2030 - $this->ustime;
            $r->freeCD->intval($cd > 0 ? (int)($cd/1000000) : 0);
            $c->addReply($r);

            /* broadcast */
            foreach ($pes as $pe) {
                if ($pe->e->rarity == 4) {
                    $smr = new SysMsgResponse;
                    $smr->format(1, 1, array($p->name, '钻石招募', array($pe->e->tag, 'orange')));
                    $this->task('broadcast-global', $smr);
                } else if ($pe->e->rarity == 5) {
                    $smr = new SysMsgResponse;
                    $smr->format(1, 2, array($p->name, '钻石招募', array($pe->e->tag, 'red')));
                    $this->task('broadcast-global', $smr);
                }
            }

            break;
        case 'e':
            foreach ($pes as $pe) {
                $r = new EquipResponse;
                $r->retval->fromPlayerEntityObject($pe);
                $c->addReply($r);

                /* broadcast */
                if ($pe->e->rarity == 3) {
                    $smr = new SysMsgResponse;
                    $smr->format(4, 1, array($p->name, array($pe->e->tag, 'purple')));
                    $this->task('broadcast-global', $smr);
                } else if ($pe->e->rarity == 4) {
                    $smr = new SysMsgResponse;
                    $smr->format(4, 1, array($p->name, array($pe->e->tag, 'orange')));
                    $this->task('broadcast-global', $smr);
                } else if ($pe->e->rarity == 5) {
                    $smr = new SysMsgResponse;
                    $smr->format(4, 2, array($p->name, array($pe->e->tag, 'red')));
                    $this->task('broadcast-global', $smr);
                }
            }

            break;
        }
    }

    // update pp resource
    $p->save();

    // sending resource response
    if ($free === false) {
        $r = new BuildingResourceResponse;
        $r->retval->fromOwnerObject($p);
        $c->addReply($r);
    }

    TaskEmitter::recruit($p, $g, $this);
    if ($g->id == 2030 || $g->id == 2031) {
        GuideModule::aspect($c, 5);
    }
    if ($this->currcmd->code == 27) {
        GuideModule::aspect($c, 8);
    }
};

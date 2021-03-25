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
use Zhanhd\ReqRes\Activity\Recruit\Request,
    Zhanhd\ReqRes\Activity\Recruit\Response,
    Zhanhd\ReqRes\Recruit\EquipResponse,
    Zhanhd\ReqRes\Recruit\HeroResponse,
    Zhanhd\ReqRes\Recruit\PropResponse,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\Object\ActivityPlan,
    Zhanhd\Config\Store,
    Zhanhd\Config\Activity,
    Zhanhd\Config\Entity                as SourceEntity,
    Zhanhd\Extension\Achievement\Module as AchievementModule;

/**
 *
 */
return function (Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }
    
    $p = $c->local->player;
    /**
     * check
     */
    $activityPlan = new ActivityPlan;
    if (false === $activityPlan->find($request->aid->intval())) {
        return $c->addReply($this->errorResponse->error('activity-plan not found'));
    }

    if ($this->ustime < $activityPlan->begin * 1000000) {
        return $c->addReply($this->errorResponse->error('activity has not yet begun'));
    }

    if ($this->ustime > $activityPlan->end * 1000000) {
        return $c->addReply($this->errorResponse->error('activity has yet end'));
    }

    $activity = Store::get('activity', $activityPlan->aid);
    if ($activity->type <> Activity::TYPE_RECRUIT) {
        return $c->addReply($this->errorResponse->error('invalid activity type'));
    }

    $g = $activity->getThing();
    if (null === $g) {
        return $c->addReply($this->errorResponse->error('notfound goods'));
    }

    /**
     * do it
     */
    $free = false;
    if ($g->gold && $free === false) {
        $ckey = $g->getCounterKey();
        $gold = $g->gold + $g->incr * $p->counterCycle->$ckey;


        if ($p->gold < $gold) {
            return $c->addReply($this->errorResponse->error('notenough diamond'));
        }

        $p->decrGold($gold);
        if ($g->incr) {
            $p->counterCycle->$ckey++;
        }
    }

    // requirements
    if ($g->requirement && $free === false) {
        foreach ($g->requirement as $eid => $num) {
            if ($p->profile->$eid < $num) {
                return $c->addReply($this->errorResponse->error('resource not enough'));
            }
        }

        foreach ($g->requirement as $eid => $num) {
            $p->profile->$eid -= $num;

            // logger
            $c->local->logger->set(null, array(
                'eid' => $eid,
                'cnt' => $num,
            ));
        }
    }

    // pick & increate entity
    $increases = (new Object)->import([
        'h' => [],
        'e' => [],
        'p' => [],
    ]);
    $p->increaseEntity(Store::get('edrop', $g->epid)->pick(), function($pe, $c) use ($increases) {
        switch ($pe->e->type) {
        case SourceEntity::TYPE_CHEST:
        case SourceEntity::TYPE_PROP:
            $increases->p->set(null, $pe);
            break;

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
            $c->addReply($r);
            break;
        case 'e':
            foreach ($pes as $pe) {
                $r = new EquipResponse;
                $r->retval->fromPlayerEntityObject($pe);
                $c->addReply($r);
            }
            break;
        case 'p':
            foreach ($pes as $pe) {
                $r = new PropResponse;
                $r->retval->fromPlayerEntityObject($pe);
                $c->addReply($r);
            }
            break;
        }
    }

    /**
     * update player gold resource
     */
    $p->save();
    $resourceResponse = new ResourceResponse;
    $resourceResponse->retval->fromOwnerObject($p);
    $c->addReply($resourceResponse);

    $key = $activityPlan->redisKey();
    $encoded = $this->redis->zScore($key, $p->id);
    $decoded = $activityPlan->decode($encoded);
    $score   = $decoded['score'] += $activity->scoreIncr;
    $this->redis->zAdd($key, $activityPlan->encode($score, $this->ustime), $p->id);

    /**
     *
     */
    $r = new Response;
    $r->aid->intval($activityPlan->id);
    $c->addReply($r);
};

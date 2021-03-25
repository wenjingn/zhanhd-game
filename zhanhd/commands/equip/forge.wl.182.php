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
use Zhanhd\ReqRes\Forge\Request,
    Zhanhd\ReqRes\Forge\Response,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\Config\Store,
    Zhanhd\Config\Entity        as SourceEntity,
    Zhanhd\Config\Forge         as SourceForge,
    Zhanhd\Object\Player\Entity as PlayerEntity,
    Zhanhd\Config\WeekMission,
    Zhanhd\Extension\WeekMission\Module as WeekMissionModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $pe = new PlayerEntity;
    if (false === $pe->findByPid($request->peid->intval(), $p->id)) {
        return $c->addReply($this->errorResponse->error('pe not found'));
    } else if (false === $pe->e->isForgeable()) {
        return $c->addReply($this->errorResponse->error('pe not forgeable'));
    } else if ($pe->property->forge == $pe->e->property->forgeMaxLvl) {
        return $c->addReply($this->errorResponse->error('pe forge level max'));
    }

    $pe->property->forge = $pe->property->forge + 1;
    if (null === ($forge = Store::get('forge', $pe->property->forge))) {
        return $c->addReply($this->errorResponse->error('forge not found'));
    }

    // requirements
    foreach (array(
        $pe->e->property->forgeProp => $forge->prop,
        6 => $forge->gold,
    ) as $eid => $num) {
        if ($p->profile->$eid < $num) {
            return $c->addReply($this->errorResponse->error('resource not enough'));
        }
    }

    foreach (array(
        $pe->e->property->forgeProp => $forge->prop,
        6 => $forge->gold,
    ) as $eid => $num) {
        $p->profile->$eid -= $num;
    }

    $pe->save();
    $p->profile->save();

    /* week mission */
    $p->counterWeekly->forge++;
    $p->counterWeekly->save();
    WeekMissionModule::trigger($p, $this, WeekMission::TYPE_FORGE, $p->counterWeekly->forge);

    /**
     * send response
     */
    $r = new ResourceResponse;
    $r->retval->fromOwnerObject($p);
    $c->addReply($r);

    $r = new Response;
    $r->peid ->intval($pe->id);
    $r->level->intval($pe->property->forge);
    $c->addReply($r);
};

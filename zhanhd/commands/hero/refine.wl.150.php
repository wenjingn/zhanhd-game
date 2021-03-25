<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Swoole\Client;

/**
 *
 */
use Zhanhd\ReqRes\Hero\Refine\Request,
    Zhanhd\ReqRes\Hero\Refine\Response,
    Zhanhd\ReqRes\PropUse\PropRemainResponse,
    Zhanhd\Object\Player\Entity as PlayerEntity,
    Zhanhd\Config\Entity        as SourceEntity,
    Zhanhd\Config\WeekMission,
    Zhanhd\Extension\WeekMission\Module as WeekMissionModule;

/**
 *
 */
$REFINE_STONE_ID = 410225;

/**
 *
 */
return function(Client $c) use ($REFINE_STONE_ID) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $pe = new PlayerEntity;
    if (false === $pe->findByPid($request->peid->intval(), $p->id)) {
        return $c->addReply($this->errorResponse->error('notfound hero'));
    }

    if ($pe->e->type != SourceEntity::TYPE_HERO) {
        return $c->addReply($this->errorResponse->error('invalid pe type'));
    }

    if ($p->profile->$REFINE_STONE_ID < 1) {
        return $c->addReply($this->errorResponse->error('notenough resource'));
    }

    $p->profile->$REFINE_STONE_ID--;
    $p->profile->save();

    $p->counterWeekly->refine++;
    $p->counterWeekly->save();
    WeekMissionModule::trigger($p, $this, WeekMission::TYPE_REFINE, $p->counterWeekly->refine);

    $refine = $pe->getRefine();
    $pe->copyProp($pe->e->random(), $refine);
    $refine->save();

    $r = new PropRemainResponse;
    $r->propId->intval($REFINE_STONE_ID);
    $r->num->intval($p->profile->$REFINE_STONE_ID);
    $c->addReply($r);

    $r = new Response;
    $r->refine->fromRefineObject($refine);
    $c->addReply($r);
};

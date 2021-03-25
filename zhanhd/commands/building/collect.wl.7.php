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
use Zhanhd\ReqRes\Building\Collect\Request,
    Zhanhd\ReqRes\Building\Collect\Response as BuildingCollectResponse,
    Zhanhd\ReqRes\Building\ResourceResponse as BuildingResourceResponse,
    Zhanhd\Extension\Achievement\Module     as AchievementModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;

    if (null === ($pb = $p->getBuilding($request->bid->intval()))) {
        return $c->addReply($this->errorResponse->error('building not found'));
    } else if ($pb->b->collectable == 0) {
        return $c->addReply($this->errorResponse->error('building not collectable'));
    }

    if (count(($collected = $pb->collect())) == 0) {
        return $c->addReply($this->errorResponse->error('nothing to collect'));
    }

    foreach ($collected as $eid => $num) {
        $p->profile->$eid += $num;

        // logger
        $c->local->logger->set(null, array(
            'eid' => $eid,
            'cnt' => $num,
        ));
    }

    $p->counterCycle->resourceCollect++;
    $p->counterCycle->save();

    $p->counter->resourceCollect++;
    $p->counter->save();
    // update pb record
    $pb->save();

    // update pp resource
    $p->profile->save();

    // trigger achievement event
    (new AchievementModule($p))->trigger((new Object)->import(array(
        'cmd'    => 'building',
        'strval' => 'collect',
        'argv'   => $collected,
    )));

    // sending collect response
    $r = new BuildingCollectResponse;
    $r->bid->intval($pb->bid);
    $r->num->intval($num);
    $r->ttfull->intval($pb->cbp->ctmax - 1);
    $c->addReply($r);

    // sending resource response
    $r = new BuildingResourceResponse;
    $r->causeId->intval($pb->bid);
    $r->retval->fromOwnerObject($p);
    $c->addReply($r);
};

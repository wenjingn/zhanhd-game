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
use Zhanhd\ReqRes\Building\Upgrade\Request,
    Zhanhd\ReqRes\Building\Upgrade\Response as BuildingUpgradeResponse,
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
    } else if (null === $pb->nbp) {
        return $c->addReply($this->errorResponse->error('already max-level'));
    } else if ($pb->getUpgradeRemainTime() > 0) {
        return $c->addReply($this->errorResponse->error('building is upgrading'));
    }

    // collect resource
    foreach ($pb->collect(true) as $eid => $num) {
        $p->profile->$eid += $num;

        // logger
        $c->local->logger->set(null, array(
            'eid' => $eid,
            'cnt' => $num,
        ));
    }

    // upgradation requirements
    foreach ($pb->nbp->upgradations as $eid => $num) {
        if ($p->profile->$eid < $num) {
            return $c->addReply($this->errorResponse->error('resource not enough'));
        }
    }

    foreach ($pb->nbp->upgradations as $eid => $num) {
        $p->profile->$eid -= $num;

        // logger
        $c->local->logger->set(null, array(
            'eid' => $eid,
            'cnt' => $num,
        ));
    }

    // update pb record
    $pb->upgrade();
    $pb->save();

    // trigger achievement event
    (new AchievementModule($p))->trigger((new Object)->import(array(
        'cmd'    => 'building',
        'strval' => 'upgrade',
    )));

    // update pp resource
    $p->profile->save();

    // sending upgrade response
    $r = new BuildingUpgradeResponse;
    $r->buildings->resize(1)->get(0)->fromPlayerBuildingObject($pb);
    $c->addReply($r);

    // sending resource response
    $r = new BuildingResourceResponse;
    $r->retval->fromOwnerObject($p);
    $c->addReply($r);
};

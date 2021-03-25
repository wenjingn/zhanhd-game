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
use Zhanhd\ReqRes\Activity\Rank\Request,
    Zhanhd\ReqRes\Activity\Rank\Response,
    Zhanhd\Config\Store,
    Zhanhd\Config\Activity,
    Zhanhd\Object\Player,
    Zhanhd\Object\ActivityPlan;

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

    $activity = Store::get('activity', $activityPlan->aid);
    if ($activity->type <> Activity::TYPE_RECRUIT) {
        return $c->addReply($this->errorResponse->error('invalid activity type'));
    }

    /**
     * do it
     */
    $key = $activityPlan->redisKey();
    $rankList = $this->redis->zrevrange($key, 0, 9, true);
    $rankSelf = $this->redis->zrevrank($key, $p->id);
    $encoded  = $this->redis->zScore($key, $p->id);
    $decoded  = $activityPlan->decode($encoded);
    $scoreSelf = $decoded['score'];

    $r = new Response;
    $r->rankSelf->fromPlayerObject($p, $rankSelf + 1, $scoreSelf);
    $r->rankList->resize(count($rankList));
    $i = 0;
    foreach ($rankList as $pid => $rank) {
        $p = new Player;
        $p->find($pid);
        $decoded = $activityPlan->decode($rank);
        
        $r->rankList->get($i)->fromPlayerObject($p, $i + 1, $decoded['score']);
        $i++;
    }

    $c->addReply($r);
};

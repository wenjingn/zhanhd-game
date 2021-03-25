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
use Zhanhd\ReqRes\FixedTimeReward\Request,
    Zhanhd\ReqRes\FixedTimeReward\Response,
    Zhanhd\Config\Store,
    Zhanhd\Extension\Reward\Module as RewardModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    if (null === ($curr = Store::get('fixedTimeReward', $request->id->intval()))) {
        return $c->addReply($this->errorResponse->error('invalid reward id'));
    }

    $p = $c->local->player;
    $k = $curr->getCounterKey();

    if ($p->counterCycle->$k) {
        return $c->addReply($this->errorResponse->error('already done'));
    }

    $d = (int) ((ustime() - $p->lastLogin) / 1000000);
    if ($curr->id == 1) {
        if (($p->counterCycle->onlineDur + $d) < $curr->sec) {
            return $c->addReply($this->errorResponse->error('cannot accept reward'));
        }
    } else {
        $robj = $curr;
        while (($robj = Store::get('fixedTimeReward', $robj->id - 1))) {
            $rk = $robj->getCounterKey();
            if (empty($p->counterCycle->$rk)) {
                return $c->addReply($this->errorResponse->error('invalid reward id'));
            }
        }

        if ($p->counterCycle->nextOnlineRewardAccepted > $d) {
            return $c->addReply($this->errorResponse->error('cannot accept reward'));
        }
    }

    if (($next = Store::get('fixedTimeReward', $curr->id + 1))) {
        $p->counterCycle->nextOnlineRewardAccepted = $next->sec + $d;
    }

    /* init response */
    $r = new Response;
    $r->id->intval($curr->id);
    RewardModule::aspect($p, $curr->source, $r->rewards, $c, $this);
    $c->addReply($r);

    $p->counterCycle->$k = 1;
    $p->counterCycle->save();
};

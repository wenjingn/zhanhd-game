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
use Zhanhd\ReqRes\RechargeReward\Request,
    Zhanhd\ReqRes\RechargeReward\Response,
    Zhanhd\Config\Store,
    Zhanhd\Extension\Reward\Module as RewardModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    if (null === ($merchandise = Store::get('merchandise', $request->id->intval()))) {
        return $c->addReply($this->errorResponse->error('invalid reward id'));
    }

    if (null === ($reward = Store::get('rechargeReward', $request->id->intval()))) {
        return $c->addReply($this->errorResponse->error('invalid reward id'));
    }

    $p = $c->local->player;
    $rk = $merchandise->getRechargeRewardKey();
    $ak = $merchandise->getRechargeRewardAcceptedKey();

    if (empty($p->counterCycle->$rk) || $p->counterCycle->$rk == $p->counterCycle->$ak) {
        return $c->addReply($this->errorResponse->error('cannot accept reward'));
    }

    if ($p->counterCycle->$ak == $reward->times) {
        return $c->addReply($this->errorResponse->error('already done'));
    }

    /* init response */
    $r = new Response;
    $r->id->intval($reward->id);
    RewardModule::aspect($p, $reward->source, $r->rewards, $c, $this);
    $c->addReply($r);

    $p->counterCycle->$ak++;
    $p->counterCycle->save();
};

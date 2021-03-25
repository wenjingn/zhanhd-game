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
use Zhanhd\ReqRes\DepositReward\Request,
    Zhanhd\ReqRes\DepositReward\Response,
    Zhanhd\Config\Store,
    Zhanhd\Extension\Reward\Module as RewardModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;

    if (null === ($dr = Store::get('deposit', $request->drid->intval()))) {
        return $c->addReply($this->errorResponse->error('invalid reward'));
    }

    $ckey = $dr->getCounterKey();
    if ($p->counter->$ckey) {
        return $c->addReply($this->errorResponse->error('already accepted reward'));
    }

    if ($p->deposit < $dr->limit) {
        return $c->addReply($this->errorResponse->error('require deposit not reach'));
    }

    $p->counter->$ckey++;
    $p->counter->save();
    $r = new Response;
    $r->drid->intval($dr->id);
    RewardModule::aspect($p, $dr->source, $r->rewards, $c, $this);
    $c->addReply($r);
};

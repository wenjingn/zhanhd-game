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
use Zhanhd\ReqRes\Invite\Reward\Request,
    Zhanhd\ReqRes\Invite\Reward\Response,
    Zhanhd\Object\Player\Coherence as PlayerCoherence,
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

    if (null === ($ir = Store::get('invite', $request->irid->intval()))) {
        return $c->addReply($this->errorResponse->error('invalid invite reward'));
    }

    $ckey = $ir->getCounterKey();
    if ($p->counter->$ckey) {
        return $c->addReply($this->errorResponse->error('invite reward already accepted'));
    }

    if (PlayerCoherence::get($this->pdo, $p->id, 'invcount') < $ir->limit) {
        return $c->addReply($this->errorResponse->error('require invcount not reach'));
    }

    $p->counter->$ckey++;
    $p->counter->save();

    $r = new Response;
    $r->irid->intval($ir->id);
    RewardModule::aspect($p, $ir->source, $r->reward, $c, $this);
    $c->addReply($r);
};

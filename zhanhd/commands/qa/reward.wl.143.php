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
use Zhanhd\ReqRes\QA\Reward\Response,
    Zhanhd\Object\Player\Counter\Cycle as PlayerCounterCycle,
    Zhanhd\Config\Store,
    Zhanhd\Extension\Reward\Module as RewardModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === $this->parseParametersNone($c)) {
        return;
    }

    $p = $c->local->player;
    /* check */
    if ($p->counterCycle->qaReward > 0) {
        return $c->addReply($this->errorResponse->error('already accepted qa-reward'));
    }

    if ($p->counterCycle->qa < PlayerCounterCycle::DAILY_QA_LIMIT) {
        return $c->addReply($this->errorResponse->error('notcomplete qa'));
    }

    if (null === ($qr = Store::get('questionReward', (integer)$p->counterCycle->qaCorrect))) {
        return $c->addReply($this->errorResponse->error('notfound qa-reward'));
    }

    $r = new Response;
    RewardModule::aspect($p, $qr->source, $r->rewards, $c, $this);
    $p->counterCycle->qaReward++;
    $p->counterCycle->save();
    $c->addReply($r);
};

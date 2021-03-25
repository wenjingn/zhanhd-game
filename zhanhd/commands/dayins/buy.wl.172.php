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
use Zhanhd\ReqRes\DayIns\BuyResponse,
    Zhanhd\ReqRes\Building\ResourceResponse;

/**
 *
 */
return function(Client $c) {
    if (-1 === $this->parseParametersNone($c)) {
        return;
    }

    $p = $c->local->player;

    if ($p->gold < 50) {
        return $c->addReply($this->errorResponse->error('notenough diamond'));
    }

    $p->decrGold(50);
    $p->counterCycle->dayinsBuy++;
    $p->save();

    $r = new ResourceResponse;
    $r->retval->fromOwnerObject($p);
    $c->addReply($r);

    $r = new BuyResponse;
    $r->times->intval($p->counterCycle->getDayInsTimes());
    $c->addReply($r);
};

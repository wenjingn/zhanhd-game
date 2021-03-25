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
use Zhanhd\ReqRes\WorldBoss\Reborn\Response,
    Zhanhd\ReqRes\Building\ResourceResponse;

/**
 *
 */
return function(Client $c){
    if (-1 === $this->parseParametersNone($c)) {
        return;
    }

    $p = $c->local->player;

    $price = 10 + $p->counterCycle->wbRebornTimes*10;
    if ($p->gold < $price) {
        return $c->addReply($this->errorResponse->error('notenough diamond'));
    }
    $p->decrGold($price);
    $p->counterCycle->wbRebornTimes++;
    $p->recent->worldboss = 0;
    $p->save();

    $r = new ResourceResponse;
    $r->retval->fromOwnerObject($p);
    $c->addReply($r);

    $r = new Response;
    $r->buyTimes->intval($p->counterCycle->wbRebornTimes);
    $c->addReply($r);
};

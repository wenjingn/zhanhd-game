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
use Zhanhd\ReqRes\Replay\Request,
    Zhanhd\ReqRes\Replay\Response,
    Zhanhd\Object\Replay;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $replay = new Replay;
    if (false === $replay->find($request->replayId->intval())) {
        return $c->addReply($this->errorResponse->error('notfound'));
    }

    if ($this->ustime < $replay->release) {
        goto deny;
    }
    if ($replay->flags == Replay::ACCESS_ALL) {
        goto ret;
    }
    if ($p->id == $replay->attacker && $replay->access & Replay::ACCESS_ATTACKER) {
        goto ret;
    }
    if ($p->id == $replay->defender && $replay->access & Replay::ACCESS_DEFENDER) {
        goto ret;
    }
    if ($p->id != $replay->attacker && $p->id != $replay->defender && Replay::ACCESS_OTHERS) {
        goto ret;
    }
deny:
    return $c->addReply($this->errorResponse->error('denied'));

ret:
    $r = new Response;
    $r->combat->decode($replay->combat);
    $c->addReply($r);
};

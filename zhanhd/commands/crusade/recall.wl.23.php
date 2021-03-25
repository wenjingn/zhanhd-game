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
use Zhanhd\ReqRes\Crusade\Recall\Request,
    Zhanhd\ReqRes\Crusade\Recall\Response,
    Zhanhd\Object\Player\Crusade    as PlayerCrusade;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p  = $c->local->player;
    $pc = new PlayerCrusade;

    if (false === $pc->find($p->id, $request->cid->intval())) {
        return $c->addReply($this->errorResponse->error('player-crusade not found'));
    }

    $pc->autoStatus();
    switch ($pc->flags) {
    case PlayerCrusade::FLAG_ACCEPTING:
    case PlayerCrusade::FLAG_DONE:
        return $c->addReply($this->errorResponse->error('player-crusade cannot be recall'));
    }

    $pc->flags   = PlayerCrusade::FLAG_DONE;
    $pc->updated = $this->ustime;
    $pc->save();

    $r = new Response;
    $r->cid->intval($pc->cid);
    $r->gid->intval($pc->gid);
    $c->addReply($r);
};

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
use Zhanhd\ReqRes\Relation\PK\Request,
    Zhanhd\ReqRes\Relation\PK\Response,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Relation as PlayerRelation,
    Zhanhd\Extension\Combat\Module as CombatModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $pr = $p->getRelation($request->fid->intval());
    if ($pr === null || $pr->isFriend() === false) {
        return $c->addReply($this->errorResponse->error('not your friend'));
    }

    $f = new Player;
    if (false === $f->find($pr->fid)) {
        return $c->addReply($this->errorResponse->error('player not exists'));
    }

    $r = new Response;
    (new CombatModule)->combat($p->getLineups('gid')->get(1), $f->getLineups('gid')->get(1), $r->combat);
    $c->addReply($r);
};

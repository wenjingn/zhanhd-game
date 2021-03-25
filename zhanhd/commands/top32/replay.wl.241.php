<?php
/**
 * $Id$
 */

/**
 *
 */
use Zhanhd\Extension\Top32,
    Zhanhd\ReqRes\Top32\Replay\Request,
    Zhanhd\ReqRes\Top32\Replay\Response;

/**
 *
 */
return function($c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $index = $request->index->intval();
    if ($index > 32 || $index < 1) {
        return $c->addReply($this->errorResponse->error('invalid range'));
    }

    $m = new Top32($this);
    if ($index >= $m->competitionFinishedCount()) {
        return $c->addReply($this->errorResponse->error('invalid range'));
    }

    if ($bin = $m->getCombatBin($index-1)) {
        $r = new Response;
        $r->combat->decode($bin);
        $c->addReply($r);
    }
};

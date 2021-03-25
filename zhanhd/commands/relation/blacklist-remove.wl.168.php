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
use Zhanhd\ReqRes\Relation\Blacklist\Remove\Request,
    Zhanhd\ReqRes\Relation\Blacklist\Remove\Response,
    Zhanhd\Object\Player\Relation as PlayerRelation;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $blacklist = $p->getBlacklist();

    $list = [];
    foreach ($request->pids as $i => $o) {
        $bid = $o->intval();
        if (false === isset($blacklist->$bid)) {
            $c->addReply($this->errorResponse->error('notfound in blacklist'));
            break;
        }

        if (isset($list[$bid])) {
            $c->addReply($this->errorResponse->error('repeat player'));
            break;
        }
        $list[$bid] = $bid;
    }

    
    $r = new Response;
    $r->pids->resize(count($list));
    $i = 0;
    foreach ($list as $bid) {
        $blacklist->$bid->remflags(PlayerRelation::FLAG_BLACK);
        $blacklist->$bid->save();

        $r->pids->get($i)->intval($bid);
        $i++;
    }

    $c->addReply($r);
};

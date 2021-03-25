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
use Zhanhd\ReqRes\Relation\Blacklist\Request,
    Zhanhd\ReqRes\Relation\Blacklist\Response,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Relation as PlayerRelation;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }
    
    $p = $c->local->player;

    $bid = $request->pid->intval();
    if ($bid == $p->id) {
        return $c->addReply($this->errorResponse->error('deprecated blacklist yourself'));
    }

    $black = new Player;
    if (false === $black->find($bid)) {
        return $c->addReply($this->errorResponse->error('notfound player'));
    }

    $pr = new PlayerRelation;
    $prfind = $pr->findByPair($p->id, $black->id);
    if ($prfind && $pr->hasflags(PlayerRelation::FLAG_BLACK)) {
        return $c->addReply($this->errorResponse->error('in your blacklist'));
    }

    if (false === $prfind) {
        $pr->pid = $p->id;
        $pr->fid = $black->id;
    }
    $pr->addflags(PlayerRelation::FLAG_BLACK);
    $pr->save();

    $r = new Response;
    $r->friend->fromRelationObject($pr, $this);
    $c->addReply($r);
};

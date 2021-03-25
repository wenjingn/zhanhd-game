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
use Zhanhd\ReqRes\Relation\BreakOff\Request,
    Zhanhd\ReqRes\Relation\BreakOff\Response,
    Zhanhd\Object\Player\Relation as PlayerRelation;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    
    /* check request */
    $size = $request->friends->size();
    if ($size == 0) {
        return $c->addReply($this->errorResponse->error('choose friends first'));
    }

    if ($size > PlayerRelation::MAX_LIMIT) {
        return $c->addReply($this->errorResponse->error('choose too much friends'));
    }

    $friends = [];
    $relations = $p->getFriends();
    foreach ($request->friends as $i => $o) {
        $fid = $o->intval();
        if (isset($friends[$fid])) {
            continue;
        }
        
        if (false === isset($relations->$fid)) {
            return $c->addReply($this->errorResponse->error('not your friend'));
        }

        $friends[$fid] = $relations->$fid;
    }

    /* breakoff relationship and sending response */
    $r = new Response;
    $r->friends->resize(count($friends));
    $i = 0;
    foreach ($friends as $fid => $pr) {
        $pr->remflags(PlayerRelation::FLAG_FRIEND);
        $pr->save();
        $r->friends->get($i)->intval($fid);

        $pr = new PlayerRelation;
        if ($pr->findByPair($fid, $p->id)) {
            $pr->remflags(PlayerRelation::FLAG_FRIEND);
            $pr->save();
            $notify = new Response;
            $notify->friends->resize(1);
            $notify->friends->get(0)->intval($p->id);
            $this->sendTo($fid, $notify);
        }
        $i++;
    }
    $c->addReply($r);
};

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
use Zhanhd\ReqRes\Relation\Confirm\Request,
    Zhanhd\ReqRes\Relation\Communicate\Response as CommunicateResponse,
    Zhanhd\ReqRes\Relation\Refuse\Response      as RefuseResponse,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Relation as PlayerRelation;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;

    /**
     * check
     */
    $size = $request->friends->size();
    if ($size === 0) {
        return $c->addReply($this->errorResponse->error('choose friends first'));
    }

    if ($size > PlayerRelation::MAX_LIMIT) {
        return $c->addReply($this->errorResponse->error('choose too much friends'));
    }

    $strangers = [];
    $confirms= $p->getRelationConfirms();

    $flag = $request->flag->intval();
    if ($flag == Request::FLAG_ACCEPT) {
        $friends = $p->getFriends();
        $count   = $friends->count();
        foreach ($request->friends as $o) {
            if ($count >= PlayerRelation::MAX_LIMIT) {
                return $c->addReply($this->errorResponse->error('number of friends reach the maximum'));
            }

            $sid = $o->intval();
            if (isset($strangers[$sid]) || isset($friends->$sid)) {
                continue;
            }

            if (false === isset($confirms->$sid)) {
                return $c->addReply($this->errorResponse->error('relation communication wrong step'));
            }

            $stranger = new Player;
            if (false === $stranger->find($sid)) {
                return $c->addReply($this->errorResponse->error('player not exists'));
            }

            if (false === $stranger->userValidateStatus()) {
                return $c->addReply($this->errorResponse->error('player not valid'));
            }

            $count++;
            $strangers[$sid] = $stranger;
        }
        
        $r = new CommunicateResponse;
        $r->friends->resize(count($strangers));
        foreach ($r->friends as $o) {
            $friend = current($strangers);
            $pr = new PlayerRelation;
            $pr->findByPair($p->id, $friend->id);
            $pr->addflags(PlayerRelation::FLAG_FRIEND);
            $pr->remflags(PlayerRelation::FLAG_CONFIRM|PlayerRelation::FLAG_WAITING);
            $pr->save();
            $o->fromRelationObject($pr, $this);
            
            $pr = new PlayerRelation;
            $pr->findByPair($friend->id, $p->id);
            $pr->addflags(PlayerRelation::FLAG_FRIEND);
            $pr->remflags(PlayerRelation::FLAG_WAITING|PlayerRelation::FLAG_CONFIRM);
            $pr->save();

            $friendsFriends = $friend->getFriends();
            if ($friendsFriends->count() >= PlayerRelation::MAX_LIMIT) {
                PlayerRelation::clearWaitingAndConfirm($this->pdo, $friend);
            }

            $notify = new CommunicateResponse;
            $notify->friends->resize(1);
            $notify->friends->get(0)->fromRelationObject($pr, $this);
            $this->sendTo($friend->id, $notify);
            next($strangers);
        }

        $c->addReply($r);
    } else {
        foreach ($request->friends as $o) {
            $sid = $o->intval();
            if (isset($strangers[$sid])) {
                continue;
            }

            if (false === isset($confirms->$sid)) {
                return $c->addReply($this->errorResponse->error('relation communication wrong step'));
            }

            $strangers[$sid] = $sid;
        }

        $r = new RefuseResponse;
        $r->strangers->resize(count($strangers));
        $i = 0;
        foreach ($strangers as $sid) {
            $pr = new PlayerRelation;
            $pr->findByPair($p->id, $sid);
            $pr->remflags(PlayerRelation::FLAG_FRIEND);
            $pr->remflags(PlayerRelation::FLAG_CONFIRM);
            $pr->save();
            $r->strangers->get($i)->fromRelationObject($pr, $this);

            $pr = new PlayerRelation;
            $pr->findByPair($sid, $p->id);
            $pr->remflags(PlayerRelation::FLAG_FRIEND);
            $pr->remflags(PlayerRelation::FLAG_WAITING);
            $pr->save();
            $i++;
        }

        $c->addReply($r);
    }
};

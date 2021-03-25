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
use Zhanhd\ReqRes\Relation\Communicate\Request,
    Zhanhd\ReqRes\Relation\Confirm\Response,
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
    if ($request->strangers->size() === 0) {
        return $c->addReply($this->errorResponse->error('choose friends first'));
    }

    if ($request->strangers->size() > PlayerRelation::MAX_LIMIT) {
        return $c->addReply($this->errorResponse->error('choose too much friends'));
    }

    $strangers = [];
    $relations = $p->getRelationShip();
    $count = 0;
    foreach ($relations as $pr) {
        if ($pr->hasflags(PlayerRelation::FLAG_FRIEND)) {
            $count++;
        }
    }
    foreach ($request->strangers as $o) {
        if ($count >= PlayerRelation::MAX_LIMIT) {
            return $c->addReply($this->errorResponse->error('number of friends reach the maximum'));
        }

        $sid = $o->intval();
        if (isset($strangers[$sid])) {
            continue;
        }

        if (isset($relations->$sid)) {
            if ($relations->$sid->hasflags(PlayerRelation::FLAG_BLACK)) {
                return $c->addReply($this->errorResponse->error('in your blacklist'));
            } else if ($relations->$sid->hasflags(PlayerRelation::FLAG_WAITING|PlayerRelation::FLAG_FRIEND)) {
                continue; 
            }
        }

        if ($sid == $p->id) {
            return $c->addReply($this->errorResponse->error('cannot add yourself'));
        }

        $stranger = new Player;
        if (false === $stranger->find($sid)) {
            return $c->addReply($this->errorResponse->error('player not exists'));
        }

        if (false === $stranger->userValidateStatus()) {
            return $c->addReply($this->errorResponse->error('player not valid'));
        }

        if ($stranger->getFriends()->count() >= PlayerRelation::MAX_LIMIT) {
            $c->addReply($this->errorResponse->error("high-limit friend-s friends"));
            continue;
        }

        if ($stranger->uid == 0) {
            continue;
        }

        $count++;
        $strangers[$sid] = $stranger;
    }

    /**
     * make friends
     * send response
     */
    foreach ($strangers as $o) {
        $pr = new PlayerRelation;
        if (false === $pr->findByPair($p->id, $o->id)) {
            $pr->pid = $p->id;
            $pr->fid = $o->id;
        }
        $pr->addflags(PlayerRelation::FLAG_WAITING);
        $pr->save();

        $pr = new PlayerRelation;
        if (false === $pr->findByPair($o->id, $p->id)) {
            $pr->pid = $o->id;
            $pr->fid = $p->id;
        }
        $pr->addflags(PlayerRelation::FLAG_CONFIRM);
        $pr->save();

        $r = new Response;
        $r->friends->resize(1);
        $r->friends->get(0)->fromRelationObject($pr, $this);
        $this->sendTo($o->id, $r);
    }
};

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
use Zhanhd\Object\Player,
    Zhanhd\Object\Player\Relation as PlayerRelation,
    Zhanhd\ReqRes\Relation\Recommend\Request,
    Zhanhd\ReqRes\Relation\Recommend\Response;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $RC = 10;
    
    $searchFields = $request->searchFields->strval();
    if (empty($searchFields)) {
        $ids = Player::recommendFriendIds($this->pdo);
        unset($ids[$p->id]);

        $relations = $p->getFriends();
        foreach ($relations as $o) {
            if (isset($ids[$o->fid])) {
                unset($ids[$o->fid]);
            }
        }

        shuffle($ids);
        $ids = array_slice($ids, 0, $RC);
    } else {
        if ($p->id == $searchFields) {
           $ids = []; 
        } else {
            $stranger = new Player;
            if ($stranger->find($searchFields)) {
                if ($stranger->uid != 0) {
                    $ids[] = $stranger->id; 
                } else {
                    $ids = [];
                }
            } else {
                $ids = Player::nameSearch($this->pdo, $searchFields);
                unset($ids[$p->id]);

                $relations = $p->getFriends();
                foreach ($relations as $o) {
                    if (isset($ids[$o->fid])) {
                        unset($ids[$o->fid]);
                    }
                }
                
                shuffle($ids);
                $ids = array_slice($ids, 0, $RC);
            }
        }
    }

    /**
     * send response
     */
    $r = new Response;
    $r->strangers->resize(count($ids));
    foreach ($r->strangers as $k => $o) {
        $pr = new PlayerRelation;
        $pr->pid = $p->id;
        $pr->fid = $ids[$k];
        $o->fromRelationObject($pr, $this);
    }
    $c->addReply($r);
};

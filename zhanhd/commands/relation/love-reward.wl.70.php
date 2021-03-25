<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Swoole\Client,
    System\Stdlib\Object;

/**
 *
 */
use Zhanhd\ReqRes\Relation\LoveReward\Request,
    Zhanhd\ReqRes\Relation\LoveReward\Response,
    Zhanhd\Config\Store,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Relation            as PlayerRelation,
    Zhanhd\Object\Player\Relation\Love       as PlayerRelationLove,
    Zhanhd\Extension\Check\Module            as CheckModule;

/**
 *
 */
return function (Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $fid  = $request->fid ->intval();
    $gear = $request->gear->intval();

    /**
     * check
     */
    $pr = $p->getRelation($fid);
    if (null === $pr || $pr->flag != PlayerRelation::FLAG_FRIEND) {
        return $c->addReply($this->errorResponse->error('not your friend'));
    }

    if (false === isset(PlayerRelation::$gears[$gear])) {
        return $c->addReply($this->errorResponse->error('gear not exists'));
    }


    $bonus = $pr->getBonus();
    
    if (null === ($bonu = $bonus->get($gear))) {
        return $c->addReply($this->errorResponse->error('love value not enough'));
    }

    if ($bonu->flag == PlayerRelationLove::FLAG_RECEIVED) {
        return $c->addReply($this->errorResponse->error('reward has been received'));
    }

    if (false === CheckModule::packageAspect($c, $this)) {
        return;
    }

    /**
     * receive bonus
     * send response
     */
    $r = new Response;
    
    $e = Store::get('entity', $bonu->eid);
    $p->increaseEntity((new Object)->import([
        $e->id => [
            'e' => $e,
            'n' => 1,
        ],            
    ]), function($pe, $r) {
        $r->entity->peid->intval($pe->id);
        $r->entity->eid ->intval($pe->eid);
    }, $r);

    $bonu->flag = PlayerRelationLove::FLAG_RECEIVED;
    $bonu->save();
    /* 四档好感奖励全领 好感度清零 */
    $allRecieved = true;
    foreach (PlayerRelation::$gears as $g => $ignore) {
        if (null === ($o = $bonus->get($g)) || $o->flag <> PlayerRelationLove::FLAG_RECEIVED) {
            $allRecieved = false;
            break;
        }
    }
    
    if ($allRecieved) {
        $pr->loveValue = 0;
        foreach ($bonus as $o) {
            $o->drop();
        }
        $pr->save();
    }
    
    $r->fid->intval($pr->fid);
    $r->love->intval($pr->loveValue);
    $r->gear->intval($gear);

    $c->addReply($r);
};

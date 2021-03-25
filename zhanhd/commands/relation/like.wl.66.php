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
    Zhanhd\Object\Player\Relation        as PlayerRelation,
    Zhanhd\Object\Player\Coherence       as PlayerCoherence,
    Zhanhd\Object\Player\Coherence\Daily as PlayerCoherenceDaily,
    Zhanhd\ReqRes\Relation\Like\Request,
    Zhanhd\ReqRes\Relation\Like\Response,
    Zhanhd\ReqRes\Relation\FriendShipUpdateResponse,
    Zhanhd\Config\WeekMission,
    Zhanhd\Extension\WeekMission\Module as WeekMissionModule;

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
    
    $relations = $p->getFriends();
    $friends = [];
    
    foreach ($request->friends as $o) {
        $fid = $o->intval();
        if (isset($friends[$fid])) {
            $c->addReply($this->errorResponse->error('repeat friend'));
            break;
        }

        if (null === ($pr = $relations->$fid)) {
            $c->addReply($this->errorResponse->error('not your friend'));
            break;
        }
        
        if ($pr->lastLikedDay() === $this->date) {
            $c->addReply($this->errorResponse->error('has clicked good today'));
            break;
        }

        if (false === PlayerCoherenceDaily::increaseFriendShip($this->pdo, $p->id, $this->date)) {
            $c->addReply($this->errorResponse->error('daily friendship reach the maximum'));
            break;
        } else {
            PlayerCoherence::increaseFriendShip($this->pdo, $p->id);
            $updateResponse = new FriendShipUpdateResponse;
            $updateResponse->flag->intval(0);
            $updateResponse->value->intval(PlayerCoherenceDaily::FRIENDSHIP_INCR);
            $c->addReply($updateResponse);

            $pr->likeTimes++;
            $pr->lastLiked = $this->ustime;
            $pr->save();
            if (PlayerCoherenceDaily::increaseFriendShip($this->pdo, $fid, $this->date)) {
                PlayerCoherence::increaseFriendShip($this->pdo, $fid);
                $notifyResponse = new FriendShipUpdateResponse;
                $notifyResponse->flag->intval(0);
                $notifyResponse->value->intval(PlayerCoherenceDaily::FRIENDSHIP_INCR);
                $this->sendTo($fid, $notifyResponse);
            }
        }
        $friends[$fid] = $pr;
        $p->counterWeekly->like++;
    }

    $p->counterWeekly->save();
    WeekMissionModule::trigger($p, $this, WeekMission::TYPE_LIKE, $p->counterWeekly->like);

    if (!empty($friends)) {
        $r = new Response;
        $r->friends->resize(count($friends));
        $i = 0;
        foreach ($friends as $o) {
            $r->friends->get($i)->intval($o->fid);
            $i++;
        }
        $c->addReply($r);
    }
};

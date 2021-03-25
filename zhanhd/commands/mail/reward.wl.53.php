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
use Zhanhd\ReqRes\RewardMail\Receive\Request,
    Zhanhd\ReqRes\RewardMail\Receive\Response,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\Object\Player\Coherence as PlayerCoherence,
    Zhanhd\ReqRes\Relation\FriendShipUpdateResponse,
    Zhanhd\Config\Store,
    Zhanhd\Config\Entity        as SourceEntity,
    Zhanhd\Object\Player\Reward    as PlayerReward,
    Zhanhd\Extension\Reward\Module as RewardModule;

/**
 *
 */
return function (Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    
    /**
     * check
     */
    $rewards = [];
    foreach ($p->getRewards(0) as $o) {
        $rewards[$o->id] = $o;
    }

    $acceptings = [];
    foreach ($request->rewardIds as $o) {
        $mailId = $o->intval();

        if (isset($acceptings[$mailId])) {
            continue;
        }

        if (false === isset($rewards[$mailId])) {
            $c->addReply($this->errorResponse->error('player-reward not found'));
            continue;
        }

        $acceptings[$mailId] = $rewards[$mailId];
    }

    if (empty($acceptings)) {
        return;
    }

    /**
     * calculate all rewards
     */
    $acceptings = array_values($acceptings);
    $rewards = [];
    $coherences = [];
    foreach ($acceptings as $o) {
        foreach ($o->getCoherences() as $k => $num) {
            if (false === isset($coherences[$k])) {
                $coherences[$k] = $num;
            } else {
                $coherences[$k]+= $num;
            }
        }
        foreach($o->getRewards() as $eid => $num) {
            if (false === isset($rewards[$eid])) {
                $rewards[$eid] = $num;
            } else {
                $rewards[$eid] += $num;
            }
        }
        $o->rewardTo($p);
    }

    /**
     *
     */
    if (!empty($rewards)) {
        $r = new Response;
        $r->rewardIds->resize(count($acceptings));
        foreach ($r->rewardIds as $i => $o) {
            $o->intval($acceptings[$i]->id);
        }
        RewardModule::aspect($p, $rewards, $r->rewards, $c, $this);
        $c->addReply($r);
    }

    /* reward coherence */
    if (empty($coherences)) return;
    foreach ($coherences as $k => $num) {
        switch ($k) {
        case 'medal':
            PlayerCoherence::increase($this->pdo, $p->id, 'friendship', $num);
            $notify = new FriendShipUpdateResponse;
            $notify->flag->intval(0);
            $notify->value->intval($num);
            $c->addReply($notify);
            break;
        }
    }
};

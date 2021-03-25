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
use Zhanhd\ReqRes\Completion\Poll\Request as PollRequest,
    Zhanhd\ReqRes\Completion\Accept\Request,
    Zhanhd\ReqRes\Completion\Accept\Response,
    Zhanhd\ReqRes\Relation\FriendShipUpdateResponse,
    Zhanhd\Object\Player\Coherence as PlayerCoherence,
    Zhanhd\Config\Store,
    Zhanhd\Extension\Reward\Module as RewardModule;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $type = $request->type->intval();
    $idx = $request->idx->intval();
    $p = $c->local->player;
    if ($type != PollRequest::TYPE_TASK && $type != PollRequest::TYPE_ROB) {
        return $c->addReply($this->errorResponse->error('invalid type'));
    }

    if ($type == PollRequest::TYPE_TASK) {
        $rewards = Store::get('completionReward', $type);
        if (false === isset($rewards[$idx])) {
            return $c->addReply($this->errorResponse->error('invalid argvs'));
        }
        $reward = $rewards[$idx];
        if ($p->counterCycle->completionTask < $reward->completion) {
            return $c->addReply($this->errorResponse->error('cannot accept reward'));
        }
        $ck = $reward->counterKey();
        if ($p->counterCycle->$ck > 0) {
            return $c->addReply($this->errorResponse->error('already done'));
        }

        $p->counterCycle->$ck++;
        $p->counterCycle->save();
        $r = new Response;
        RewardModule::aspect($p, $reward->reward, $r->reward, $c, $this);
        $c->addReply($r);
    } else {
        if ($idx < 1 || $idx > 4) {
            return $c->addReply($this->errorResponse->error('invalid argvs'));
        }
        $completion = $idx*5;
        if ($p->counterCycle->robSuccessTimes < $completion) {
            return $c->addReply($this->errorResponse->error('cannot accept reward'));
        }
        $ck = sprintf('completion-%d', $type*100+$idx);
        if ($p->counterCycle->$ck > 0) {
            return $c->addReply($this->errorResponse->error('already done'));
        }

        $p->counterCycle->$ck++;
        $p->counterCycle->save();
        PlayerCoherence::increase($this->pdo, $p->id, 'friendship', $completion);
        $r = new FriendShipUpdateResponse;
        $r->flag->intval(0);
        $r->value->intval($completion);
        $c->addReply($r);
        $r = new Response;
        $c->addReply($r);
    }
};

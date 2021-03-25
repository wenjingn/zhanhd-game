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
use Zhanhd\ReqRes\Act\DiaRec\Reward\Request,
    Zhanhd\ReqRes\Act\DiaRec\Reward\Response,
    Zhanhd\Config\Store,
    Zhanhd\Config\Activity,
    Zhanhd\Object\ActivityPlan,
    Zhanhd\Object\ActivityHistory,
    Zhanhd\Extension\Reward\Module as RewardModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    if (null === ($actdiarec = Store::get('actdiarec', $request->rid->intval()))) {
        return $c->addReply($this->errorResponse->error('notfound actdiarec-reward'));
    }

    $ap = new ActivityPlan;
    if (false === $ap->findByType($this->ustime/1000000, Activity::TYPE_DIAREC)) {
        return $c->addReply($this->errorResponse->error('notfound activity-plan'));
    }

    $ah = new ActivityHistory;
    if (false === $ah->find($ap->id, $p->id)) {
        $ah->aid = $ap->id;
        $ah->pid = $p->id;
    }
    if ($ah->profile->{$actdiarec->id}) {
        return $c->addReply($this->errorResponse->error('already accepted reward'));
    }

    $ah->profile->{$actdiarec->id} = 1;
    $ah->profile->save();
    $r = new Response;
    $r->rid->intval($actdiarec->id);
    RewardModule::aspect($p, $actdiarec->rewards, $r->rewards, $c, $this);
    $c->addReply($r);
};

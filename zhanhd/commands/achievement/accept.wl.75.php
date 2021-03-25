<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Stdlib\Object,
    System\Swoole\Client;

/**
 *
 */
use Zhanhd\ReqRes\Achievement\AcceptRequest as Request,
    Zhanhd\ReqRes\Achievement\AcceptResponse,
    Zhanhd\ReqRes\Achievement\UpdateResponse,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\Config\Store,
    Zhanhd\Config\Entity,
    Zhanhd\Config\Achievement,
    Zhanhd\Object\Player\Achievement        as PlayerAchievement,
    Zhanhd\Extension\Reward\Module          as RewardModule,
    Zhanhd\Extension\Check\Module           as CheckModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;

    if (null === ($a = Store::get('achievement', $request->aid->intval()))) {
        return $c->addReply($this->errorResponse->error('invalid achievement'));
    }

    $pa = new PlayerAchievement;
    if (false === $pa->find($p->id, $a->id) || $pa->flags <> PlayerAchievement::FLAG_INIT) {
        return $c->addReply($this->errorResponse->error('invalid achievement'));
    }

    /* confirm cycle-type achievement */
    if ($a->type == Achievement::TYPE_CYCLE && $a->intval && $a->intval > $p->counterCycle->{$a->getCounterKey()}) {
        return $c->addReply($this->errorResponse->error('achievement not done'));
    }

    /* memcard is special */
    if ($a->cmd == 'memcard') {
        /* memcard may expired */
        if ($p->profile->monthlyCardExpire < $this->ustime) {
            $p->profile->monthlyCardExpire = 0;
            return $c->addReply($this->errorResponse->error('memcard expired'));
        }
    }

    $rewards = $a->getRewards();
    $types = [
        Entity::TYPE_HERO,
        Entity::TYPE_WEAPON,
        Entity::TYPE_ARMOR,
        Entity::TYPE_HORSE,
        Entity::TYPE_JEWEL,
    ];
    $packageCheck = false;
    foreach ($rewards as $k => $v) {
        if (in_array(Store::get('entity', $k)->type, $types)) {
            $packageCheck = true;
            break;
        }
    }

    if ($packageCheck && false === CheckModule::packageAspect($c, $this)) {
        return;
    }

    $r = new AcceptResponse;
    $r->aid    ->intval($a->id);
    RewardModule::aspect($p, $rewards, $r->rewards, $c, $this);
    $c->addReply($r);

    $pa->flags = PlayerAchievement::FLAG_DONE;
    $pa->save();
    if ($a->type == Achievement::TYPE_CYCLE) {
        $p->counterCycle->completionTask++;
        $p->counterCycle->save();
    }
    $r = new UpdateResponse;
    $r->notify->resize(1);
    $r->notify->get(0)->fromOwnerObject($a, $p, $pa);
    $c->addReply($r);
};

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
use Zhanhd\ReqRes\Rob\Revenge\Request,
    Zhanhd\ReqRes\Rob\Revenge\Response,
    Zhanhd\ReqRes\Relation\FriendShipUpdateResponse,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Rob\Log,
    Zhanhd\Object\Player\Coherence as PlayerCoherence,
    Zhanhd\Extension\Hero\Module   as HeroModule,
    Zhanhd\Extension\Reward\Module as RewardModule,
    Zhanhd\Extension\Combat\Module as CombatModule,
    Zhanhd\Extension\Check\Module  as CheckModule,
    Zhanhd\Extension\Rob           as RobModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $log = new Log;
    if (false === $log->find($request->logid->intval()) || $p->id != $log->pid) {
        return $c->addReply($this->errorResponse->error('notfound'));
    }
    if ($log->retval) {
        return $c->addReply($this->errorResponse->error('deprecated'));
    }

    $attacker = $p->getLineups('gid')->get(1);
    if (false === CheckModule::lineupAspect($c, $this, $attacker, 5)) {
        return;
    }

    HeroModule::upgradeAspect($c, $this, $attacker, 0, 5);

    $robber = new Player;
    $robber->find($log->robber);
    $defender = $robber->getLineups('gid')->get(1);

    $r = new Response;
    (new CombatModule)->combat($attacker, $defender, $r->combat);
    if ($r->combat->win->intval()) {
        $log->retval |= Log::RETVAL_REVENGE;
        $log->save();
        $rewards = RobModule::robResourceAddition($robber->robResource(), $attacker->getPower(), $defender->getPower());
        RewardModule::aspect($p, $rewards, $r->reward, $c, $this);
        PlayerCoherence::increase($this->pdo, $p->id, 'friendship', 1);
        $notify = new FriendShipUpdateResponse;
        $notify->flag->intval(0);
        $notify->value->intval(1);
        $c->addReply($notify);
    }
    $r->log->fromObject($log, $robber);
    $c->addReply($r);
};

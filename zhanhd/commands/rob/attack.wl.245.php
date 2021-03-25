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
use Zhanhd\ReqRes\Rob\Attack\Request,
    Zhanhd\ReqRes\Rob\Attack\Response,
    Zhanhd\ReqRes\Relation\FriendShipUpdateResponse,
    Zhanhd\ReqRes\Rob\Log\Notify,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Counter\Cycle as PlayerCounterCycle,
    Zhanhd\Object\Player\Coherence       as PlayerCoherence,
    Zhanhd\Object\Player\Coherence\Daily as PlayerCoherenceDaily,
    Zhanhd\Object\Player\Rob\Tarlist,
    Zhanhd\Object\Player\Rob\Target,
    Zhanhd\Object\Player\Rob\Log,
    Zhanhd\Object\Replay,
    Zhanhd\Extension\Check\Module  as CheckModule,
    Zhanhd\Extension\Combat\Module as CombatModule,
    Zhanhd\Extension\Reward\Module as RewardModule,
    Zhanhd\Extension\Hero\Module   as HeroModule,
    Zhanhd\Extension\Rob           as RobModule,
    Zhanhd\Extension\Achievement\Module as AchievementModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    if ($p->counterCycle->robSuccessTimes >= PlayerCounterCycle::DAILY_ROB_LIMIT) {
        return $c->addReply($this->errorResponse->error('daily-limit times'));
    }

    $tid = $request->pid->intval();
    $target = new Target;
    if (false === $target->find($p->id, $tid)) {
        return $c->addReply($this->errorResponse->error('notfound'));
    }

    if ($target->status) {
        return $c->addReply($this->errorResponse->error('deprecated'));
    }

    $attacker = $p->getLineups('gid')->get(1);
    if (false === CheckModule::lineupAspect($c, $this, $attacker, 5)) {
        return;
    }

    $tarlist = new Tarlist;
    $tarlist->find($p->id);
    if (false === PlayerCoherenceDaily::increase($this->pdo, $target->tid, $this->date, 'robbed', 1, 20)) {
        $target->drop();
        $tid = Player::randomIds($this->pdo, 1, ['id!='.$p->id, 'id!='.$tid]);
        if (!empty($tid)) {
            $tid = array_pop($tid);
            $target->pid = $p->id;
            $target->tid = $tid;
            $target->save();
        } else {
            $tarlist->total--;
        }
        if ($tarlist->total == $tarlist->robbed) {
            $ranlist = Player::randomIds($this->pdo, Tarlist::MAXCOUNT, ['id!='.$p->id]);
            $targets = $tarlist->genTargets($ranlist);
        } else {
            $targets = $tarlist->getTargets();
        }
        $r = new Response;
        $r->robSuccessTimes->intval($p->counterCycle->robSuccessTimes);
        $r->targets->resize($tarlist->total);
        foreach ($r->targets as $i => $o) {
            $o->fromObject($targets->get($i), $attacker->power);
        }
        return $c->addReply($r);
    }

    HeroModule::upgradeAspect($c, $this, $attacker, 0, 5);
    $t = new Player;
    $t->find($target->tid);
    $defender = $t->getLineups('gid')->get(1);

    $r = new Response;
    (new CombatModule)->combat($attacker, $defender, $r->combat);
    $replay = new Replay;
    $replay->attacker = $p->id;
    $replay->defender = $t->id;
    $replay->combat   = $r->combat->encode();
    $replay->access   = Replay::ACCESS_ALL;
    $replay->save();
    $roblog = new Log;
    $roblog->pid = $t->id;
    $roblog->robber = $p->id;
    $roblog->replay = $replay->id;
    if ($r->combat->win->intval()) {
        $rewards = RobModule::robResourceAddition($t->robResource(), $attacker->getPower(), $defender->getPower());
        $roblog->setResources($rewards);
        RewardModule::aspect($p, $rewards, $r->reward, $c, $this);

        $p->counterCycle->robSuccessTimes++;
        $p->counterCycle->save();
        
        $tarlist->robbed++;
        if ($tarlist->robbed == $tarlist->total) {
            $tarlist->dropTargets();
            $ranlist = Player::randomIds($this->pdo, Tarlist::MAXCOUNT, ['id!='.$p->id]);
            $targets = $tarlist->genTargets($ranlist);
            $r->targets->resize($tarlist->total);
            foreach ($r->targets as $i => $o) {
                $o->fromObject($targets->get($i), $attacker->power);
            }
        } else {
            $tarlist->save();
            $target->status = 1;
            $target->save();
        }
        
        $notify = new Notify;
        $notify->log->fromObject($roblog, $p);
        $this->sendTo($t->id, $notify);
    } else {
        $roblog->retval |= Log::RETVAL_DEFEND;
        PlayerCoherence::increase($this->pdo, $t->id, 'friendship', 5);
        $notify = new FriendShipUpdateResponse;
        $notify->flag->intval(0);
        $notify->value->intval(5);
        $this->sendTo($t->id, $notify);
    }
    $roblog->save();
    $c->addReply($r);
    (new AchievementModule($p))->trigger((new Object)->import(array(
        'cmd' => 'rob',
    )));

};

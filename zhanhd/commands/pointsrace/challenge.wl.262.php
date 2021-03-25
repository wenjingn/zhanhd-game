<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Swoole\Client,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\ReqRes\PointsRace\Challenge\Response,
    Zhanhd\ReqRes\PointsRace\Tarlist\Response as TarlistResponse,
	Zhanhd\ReqRes\PointsRace\Ranks\Response as RanksResponse,
    Zhanhd\ReqRes\Relation\FriendShipUpdateResponse,
	Zhanhd\Object\Player,
    Zhanhd\Object\Player\Lineup as PlayerLineup,
    Zhanhd\Object\PointsRace,
    Zhanhd\Object\PointsRace\Daily,
    Zhanhd\Object\PointsRace\Target,
    Zhanhd\Object\Player\Coherence as PlayerCoherence,
    Zhanhd\Extension\PointsRace as PointsRaceModule,
    Zhanhd\Extension\Combat\Module as CombatModule,
    Zhanhd\Extension\Reward\Module as RewardModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new U64))) {
        return;
    }
    
    $p = $c->local->player;
    $m = new PointsRaceModule($this);

    $tid = $request->intval();
    $t = new Target;
    if (false === $t->find($m->cycle, $p->id, $tid)) {
        return $c->addReply($this->errorResponse->error('notfound'));
    }

    if ($t->status) {
        return $c->addReply($this->errorResponse->error('deprecated'));
    }

    $race = new PointsRace;
    if (false === $race->find($m->cycle, $p->id)) {
        $race->cycle = $m->cycle;
        $race->pid = $p->id;
    }
    $raceDaily = new Daily;
    if (false === $raceDaily->find($m->cycle, $m->cday, $p->id)) {
        $raceDaily->cycle = $m->cycle;
        $raceDaily->cday = $m->cday;
        $raceDaily->pid  = $p->id;
    }

    if ($raceDaily->challenged > 24) {
        return $c->addReply($this->errorResponse->error('daily-limit times'));
    }
    $pay = $raceDaily->challenged < 20 ? false : true;
    if ($pay && $p->gold < 50) {
        return $c->addReply($this->errorResponse->error('notenough diamond'));
    }

    $trace = new PointsRace;
    $trace->find($m->cycle, $t->tid);
    $attacker = $p->getLineup(1);
    $defender = new PlayerLineup;
    $defender->find($t->tid, 1);
    $r = new Response;
    (new CombatModule)->combat($attacker, $defender, $r->combat, function($attacker, $defender) use ($race, $trace) {
        if ($race->buff) {
            foreach ($attacker as $o) {
                $o->pointsRaceBuff = $race->buff;
            }
        }
        if ($trace->buff) {
            foreach ($defender as $o) {
                $o->pointsRaceBuff = $trace->buff;
            }
        }
    });

    if ($pay) $p->gold -= 50;
    $raceDaily->challenged++;
    $bonu = false;
    $score = $m->score($p->id);
    if ($r->combat->win->intval()) {
        $incrScore = $m->getWinScore($score);
        $score += $incrScore;
        $m->incr($p->id, $incrScore);
        PlayerCoherence::increase($this->pdo, $p->id, 'friendship', $incrScore);
        $notify = new FriendShipUpdateResponse;
        $notify->value->intval($incrScore);
        $c->addReply($notify);

        $raceDaily->conswin++;
        if ($raceDaily->conswin%3 == 0) {
            $incrScore = $m->getConswinScore($score, $raceDaily->conswin/3);
            $score += $incrScore;
            $m->incr($p->id, $incrScore);
            PlayerCoherence::increase($this->pdo, $p->id, 'friendship', $incrScore);
            $notify = new FriendShipUpdateResponse;
            $notify->value->intval($incrScore);
            $c->addReply($notify);
        }
        $race->cwin++;
        $rewards = $m->getCycleWinRewards($race->cwin);
        if (!empty($rewards)) {
            $bonu = true;
            RewardModule::aspect($p, $rewards, $r->reward, $c, $this);
        }
        $race->listWin++;
        if ($race->listWin == $race->listTotal) {
            $race->dropTargets();
            $list = $m->genlist($p->id);
            $targets = $race->genTargets($list);
            $notify = new TarlistResponse;
            $notify->targets->resize($targets->count());
            foreach ($notify->targets as $i => $o) {
                $target = $targets->get($i);
                $o->fromObject($target, $m->score($target->tid));
            }
            $c->addReply($notify);
        } else {
            $t->status = 1;
            $t->save();
        }
    } else {
        $raceDaily->conswin = 0;
    }
    $race->challenged++;
    $race->save();
    $m->updatePower($p->id, (int)($score*100/$race->challenged));
    $r->score->intval($score);
	$rank = $m->rank($p->id);
    $r->rank->intval($rank);
    $raceDaily->save();
    $p->save();
    $c->addReply($r);
	if ($r->combat->win->intval() && $rank <= 10) {
		$ranks = $m->ranks();
		$r = new RanksResponse;
		$r->ranks->resize(count($ranks));
		$i = 0;
		foreach ($ranks as $pid => $score) {
			$p = new Player;
			$p->find($pid);
			$r->ranks->get($i)->fromPlayerObject($p, $i+1, 0, $score);
			$i++;
		}
		$this->task('broadcast-global', $r);
	}
};

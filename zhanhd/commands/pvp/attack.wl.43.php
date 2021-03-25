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
use Zhanhd\ReqRes\PvpRank\Attack\Request,
    Zhanhd\ReqRes\PvpRank\Attack\Response,
    Zhanhd\ReqRes\SysMsgResponse,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Counter\Cycle  as PlayerCounterCycle,
    Zhanhd\Config\NewzoneMission,
    Zhanhd\Config\WeekMission,
    Zhanhd\Extension\PvpRank\Module     as PvpRankModule,
    Zhanhd\Extension\Combat\Module      as CombatModule,
    Zhanhd\Extension\Achievement\Module as AchievementModule,
    Zhanhd\Extension\NewzoneMission\Module as NewzoneMissionModule,
    Zhanhd\Extension\WeekMission\Module as WeekMissionModule,
    Zhanhd\Extension\Unlock,
    Zhanhd\Extension\Mail\Module        as MailModule;

/**
 * @var array
 */
$disableTimes = [
    2200 => 2220,
];

/**
 *
 */
return function(Client $c) use ($disableTimes) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $curr = date('Gi', $this->ustime / 1000000);
    foreach ($disableTimes as $from => $to) {
        if ($curr >= $from && $curr <= $to) {
            return $c->addReply($this->errorResponse->error('cannot attack now'));
        }
    }

    $attacker = $c->local->player;
    $key = sprintf('unlocked-%d', Unlock::PVPUNLOCK_ID);
    if (!$attacker->profile->$key) {
        return $c->addReply($this->errorResponse->error('locked'));
    }

    if ($attacker->counterCycle->pvp >= PlayerCounterCycle::DAILY_PVP_LIMIT) {
        return $c->addReply($this->errorResponse->error('pvp times reach max'));
    }

    if (intval($attacker->id) === $request->pid->intval()) {
        return $c->addReply($this->errorResponse->error('cannot attack yourself'));
    }

    $defender = new Player;
    if (false === $defender->find($request->pid->intval()) || ($defender->uid && !$defender->profile->$key)) {
        return $c->addReply($this->errorResponse->error('target not found'));
    }

    $pvp = new PvpRankModule;
    $attackerRank = $pvp->rank($attacker);
    $defenderRank = $pvp->rank($defender);
    if ($defenderRank <> $request->rank->intval()) {
        return $c->addReply($this->errorResponse->error('target rank has changed'));
    }

    $attackerLineup = $attacker->getLineups('gid')->get(1);
    if (false === $attackerLineup->getCaptain()) {
        return $c->addReply($this->errorResponse->error('captain cannot be empty'));
    }

    $defenderLineup = $defender->getLineups('gid')->get(1);
    if (false === $defenderLineup->getCaptain()) {
        return $c->addReply($this->errorResponse->error('invalid target'));
    }

    $r = new Response;
    $c->addReply($r);

    (new CombatModule)->combat($attackerLineup, $defenderLineup, $r->combat);

    if ($r->combat->win->intval() == 0) {
        // trigger achievement event
        (new AchievementModule($attacker))->trigger((new Object)->import(array(
            'cmd'    => 'pvp',
            'strval' => 'fail',
        )));
    } else {
        $attacker->counterWeekly->pvpwin++;
        $attacker->counterWeekly->save();
        WeekMissionModule::trigger($attacker, $this, WeekMission::TYPE_PVPWIN, $attacker->counterWeekly->pvpwin);
    }

    MailModule::pvpDefendAspect($r->combat->win->intval(), $attacker, $attackerRank, $defender, $defenderRank);

    $attacker->counterCycle->pvp++;
    $attacker->counterCycle->save();
    $attacker->counter->pvp++;
    $attacker->counter->save();

    if ($r->combat->win->intval() === 1 && $attackerRank > $defenderRank) {
        if (false === $pvp->exchange($attacker, $defender)) {
            return $c->addReply($this->errorResponse->error('update pvp rank failed'));
        }

        $rank = $defenderRank;

        /* broadcast */
        if ($rank <= 10) {
            $smr = new SysMsgResponse;
            $smr->format(5, 2, array($attacker->name, $defender->name, $rank));
            $this->task('broadcast-global', $smr);
        }
    } else {
        $rank = $attackerRank;
    }

    $r->rank->intval($rank);
    NewzoneMissionModule::trigger($attacker, $this, NewzoneMission::TYPE_PVPRANK, $rank);
    $r->num->intval(PlayerCounterCycle::DAILY_PVP_LIMIT - $attacker->counterCycle->pvp);
};

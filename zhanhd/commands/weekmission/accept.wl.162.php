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
use Zhanhd\ReqRes\WeekMission\Accept\Request,
    Zhanhd\ReqRes\WeekMission\Accept\Response,
    Zhanhd\Config\Store,
    Zhanhd\Config\WeekMission,
    Zhanhd\Object\Player\WeekMission    as PlayerWeekMission,
    Zhanhd\Extension\WeekMission\Module as WeekMissionModule,
    Zhanhd\Extension\Reward\Module      as RewardModule;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $mid = $request->mid->intval();
    if (null === ($m = Store::get('weekMission', $mid))) {
        return $c->addReply($this->errorResponse->error('notfound weekmission'));
    }

    WeekMissionModule::syncWeek($this);
    if (false === isset(WeekMissionModule::$weekTypes[$m->type])) {
        return $c->addReply($this->errorResponse->error('notfound weekmission'));
    }

    $pm = new PlayerWeekMission;
    if (false === $pm->find($p->id, $this->week, $m->id) || $pm->flag == PlayerWeekMission::FLAG_INIT) {
        return $c->addReply($this->errorResponse->error('notdone weekmission'));
    }

    if ($pm->flag == PlayerWeekMission::FLAG_ACCEPT) {
        return $c->addReply($this->errorResponse->error('already-accepted weekmission'));
    }

    $pm->flag = PlayerWeekMission::FLAG_ACCEPT;
    $pm->save();

    $r = new Response;
    $r->mid->intval($m->id);
    RewardModule::aspect($p, $m->rewards, $r->rewards, $c, $this);
    $c->addReply($r);
};

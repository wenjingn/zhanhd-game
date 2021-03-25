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
use Zhanhd\ReqRes\NewzoneMission\Accept\Request,
    Zhanhd\ReqRes\NewzoneMission\Accept\Response,
    Zhanhd\Config\Store,
    Zhanhd\Object\Player\NewzoneMission as PlayerNewzoneMission,
    Zhanhd\Extension\Reward\Module as RewardModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $day = $request->day->intval();
    $idx = $request->idx->intval();
    $mid = $day*100 + $idx;

    if (null === ($m = Store::get('newzoneMission', $mid))) {
        return $c->addReply($this->errorResponse->error('notfound newzonemission'));
    }

    if ($day > $this->getDayFromZoneOpen()) {
        return $c->addReply($this->errorResponse->error('locked newzonemission'));
    }

    $pm = new PlayerNewzoneMission;
    if (false === $pm->find($p->id, $m->id) || $pm->flag == PlayerNewzoneMission::FLAG_INIT) {
        return $c->addReply($this->errorResponse->error('notdone newzonemission'));
    }

    if ($pm->flag == PlayerNewzoneMission::FLAG_ACCEPT) {
        return $c->addReply($this->errorResponse->error('already-accepted newzonemission'));
    }

    $pm->flag = PlayerNewzoneMission::FLAG_ACCEPT;
    $pm->save();

    $r = new Response;
    $r->day->intval($day);
    $r->idx->intval($idx);
    RewardModule::aspect($p, $m->rewards, $r->rewards, $c, $this);
    $c->addReply($r);
};

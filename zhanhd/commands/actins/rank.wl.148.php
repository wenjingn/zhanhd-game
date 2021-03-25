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
use Zhanhd\Object\Player,
    Zhanhd\ReqRes\ActIns\Rank\Response,
    Zhanhd\Extension\ActIns\Module;

/**
 *
 */
return function(Client $c) {
    if (-1 === $this->parseParametersNone($c)) {
        return;
    }

    $r = new Response;
    /* daily */
    $r->idaily->rank->intval(Module::rank($this->redis, $this->week, $c->local->player->id));
    $r->idaily->floor->intval(Module::getScore($this->redis, $this->week, $c->local->player->id));
    $rank = Module::rankList($this->redis, $this->week);
    $r->daily->resize(count($rank));
    foreach ($r->daily as $i => $o) {
        $pid = key($rank);
        $p = new Player;
        $p->find($pid);
        $o->rank->intval($i+1);
        $o->floor->intval(current($rank));
        $o->leader->fromPlayerObject($p);
        next($rank);
    }

    /* weekly */
    $lastWeek = $this->week-1;
    $r->iweekly->rank->intval(Module::rank($this->redis, $lastWeek, $c->local->player->id));
    $r->iweekly->floor->intval(Module::getScore($this->redis, $lastWeek, $c->local->player->id));
    $rank = Module::rankList($this->redis, $lastWeek);
    $r->weekly->resize(count($rank));
    foreach ($r->weekly as $i => $o) {
        $pid = key($rank);
        $p = new Player;
        $p->find($pid);
        $o->rank->intval($i+1);
        $o->floor->intval(current($rank));
        $o->leader->fromPlayerObject($p);
        next($rank);
    }

    $c->addReply($r);
};

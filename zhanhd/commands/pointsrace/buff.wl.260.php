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
use Zhanhd\ReqRes\PointsRace\Buff\Request,
    Zhanhd\ReqRes\PointsRace\Buff\Response,
    Zhanhd\Object\PointsRace,
    Zhanhd\Extension\PointsRace as PointsRaceModule;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }
    
    $p = $c->local->player;
    $buff = $request->buff->intval();
    if ($buff < 1 || $buff > 4) {
        return $c->addReply(new Response);
    }

    $m = new PointsRaceModule($this);
    $race = new PointsRace;
    if (false === $race->find($m->cycle, $p->id)) {
        $race->cycle = $m->cycle;
        $race->pid = $p->id;
    }

    if ($race->buff == 0) {
        $race->buff = $buff;
        $race->save();
    }

    $r = new Response;
    $r->buff->intval($race->buff);
    $c->addReply($r);
};

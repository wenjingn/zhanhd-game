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
use Zhanhd\ReqRes\NewzoneMission\Poll\Request,
    Zhanhd\ReqRes\NewzoneMission\Poll\Response,
    Zhanhd\Config\Store;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $day = $request->day->intval();
    $zoneday = $this->getDayFromZoneOpen();

    if ($zoneday > 7) {
        return $c->addReply($this->errorResponse->error('expire newmission'));
    }

    if ($day > $zoneday) {
        return $c->addReply($this->errorResponse->error('invalid newmission day'));
    }

    $r = new Response;
    $r->day->intval($zoneday);
    if ($day == $zoneday) {
        return $c->addReply($r);
    }
    
    $missions = [];
    foreach (Store::get('newzoneMission') as $m) {
        if ($m->getDay() == $zoneday) {
            $missions[] = $m;
        }
    }

    $r->newzoneMissions->resize(count($missions));
    foreach ($r->newzoneMissions as $i => $o) {
        $o->fromObject($missions[$i], $p);
    }
    $c->addReply($r);
};

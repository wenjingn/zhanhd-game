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
use Zhanhd\ReqRes\WorldBoss\Rank\Response,
    Zhanhd\Object\Player,
    Zhanhd\Extension\WorldBoss\Module as WorldBossModule;

/**
 *
 */
return function(Client $c){
    if (-1 === $this->parseParametersNone($c)) {
        return;
    }

    $m = new WorldBossModule($this);
    if ($m->time < $m->begin) {
        //return $c->addReply($this->errorResponse->error('invalid worldboss time'));
    }

    $ranklist = $m->getRankList(0, 9);
    
    $r = new Response;
    $r->ranklist->resize(count($ranklist));
    $i = 0;
    foreach ($ranklist as $pid => $damage) {
        $p = new Player;
        $p->find($pid);
        $o = $r->ranklist->get($i);
        $o->nick->strval($p->name);
        $o->rank->intval($i+1);
        $o->dmg->intval($damage);
        $i++;
    }
    $c->addReply($r);
};

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
use Zhanhd\ReqRes\PointsRace\Ranks\Response,
    Zhanhd\Object\Player,
    Zhanhd\Extension\PointsRace as PointsRaceModule;

/**
 *
 */
return function(Client $c){
    if (-1 === $this->parseParametersNone($c)) {
        return;
    }

    $m = new PointsRaceModule($this);
    $ranks = $m->ranks();

    $r = new Response;
    $r->ranks->resize(count($ranks));
    $i = 0;
    foreach ($ranks as $pid => $score) {
        $p = new Player;
        $p->find($pid);
        $l = $p->getLineup(1);
        $r->ranks->get($i)->fromPlayerObject($p, $i+1, $l->lvlsum, $score);
        $i++;
    }
    $c->addReply($r);
};

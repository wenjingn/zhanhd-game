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
    Zhanhd\ReqRes\PvpRank\RankResponse,
    Zhanhd\Extension\PvpRank\Module as PvpRankingModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === $this->parseParametersNone($c)) {
        return;
    }

    $p = $c->local->player;
    $m = new PvpRankingModule;

    $mrank = 0;
    $range = $m->range(1, 10);

    $r = new RankResponse;
    $r->ranks->resize(count($range));
    $r->lineups->resize(count($range));
    $i = 0; foreach ($range as $pid => $rank) {
        $t = new Player;
        if ($t->find($pid)) {
            $r->ranks->get($i)->fromPlayerObject($t, $rank);
            $r->lineups->get($i)->fromObject($t->getLineup(1));
        }

        if ($pid == $p->id) {
            $mrank = $rank;
        }

        $i++;
    }

    if ($mrank == 0) {
        $mrank = $m->rank($p);
    }

    $r->myRank ->intval($mrank);
    $r->myPower->intval($p->getLineup(1)->power);

    $c->addReply($r);
};

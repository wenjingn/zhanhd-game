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
    Zhanhd\ReqRes\PvpRank\TargetResponse,
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
    $rank = $m->rank($p);
    $n = 5;

    if ($rank < $n + 2) {
        $range = $m->range(1, $n + 1);

        // no need to show current player
        if (isset($range[$p->id])) {
            unset($range[$p->id]);
        }
    } else {
        $range = $m->range(max(1, $rank - 100), $rank - 1);
    }

    if (count($range) > $n) {
        $temp = array_keys($range);
        shuffle($temp);
        $range = array_intersect_key($range, array_flip(array_slice($temp, mt_rand(0, count($range) - $n), $n)));
    }

    $r = new TargetResponse;
    $r->rank->intval($rank);
    $r->targets->resize(count($range));

    $i = 0; foreach ($range as $pid => $rank) {
        $t = new Player;
        if ($t->find($pid)) {
            $r->targets->get($i)->fromPlayerObject($t, $rank);
        }
        $i++;
    }

    $c->addReply($r);
};

<?php
/**
 * $Id$
 */

/**
 *
 */
use Zhanhd\Extension\Top32,
    Zhanhd\ReqRes\Top32\Poll\Response;

/**
 *
 */
return function($c){
    if (-1 === $this->parseParametersNone($c)) {
        return;
    }

    $r = new Response;
    $m = new Top32($this);
    $champion = $m->getChampion();
    if ($champion) {
        $r->champion->decode($champion);
    }

    $m->getCompetitionInfo($r->status);
    $c->addReply($r);
};

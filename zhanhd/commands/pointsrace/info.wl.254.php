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
use Zhanhd\ReqRes\PointsRace\InfoResponse,
    Zhanhd\Object\PointsRace,
    Zhanhd\Object\PointsRace\Daily as PointsRaceDaily,
    Zhanhd\Extension\PointsRace    as PointsRaceModule;

/**
 *
 */
return function(Client $c){
    if (-1 === $this->parseParametersNone($c)) {
        return;
    }

    $p = $c->local->player;
    $m = new PointsRaceModule($this);
    $race = new PointsRace;
    if (false === $race->find($m->cycle, $p->id)) {
        $race->cycle = $m->cycle;
        $race->pid = $p->id;
        $race->save();
        
        $m->add($p->id);
    }
    $raceDaily = new PointsRaceDaily;
    if (false === $raceDaily->find($m->cycle, $m->cday, $p->id)) {
        $raceDaily->cycle = $m->cycle;
        $raceDaily->cday  = $m->cday;
        $raceDaily->pid   = $p->id;
        $raceDaily->save();
    }
    
    $r = new InfoResponse;
    $r->buff->intval($race->buff);
    $r->cwin->intval($race->cwin);
    $r->score->intval($m->score($p->id));
    $r->rank->intval($m->rank($p->id));

    $r->conswin->intval($raceDaily->conswin);
    $r->challenged->intval($raceDaily->challenged);
    $r->refreshed->intval($raceDaily->refreshed);
    $c->addReply($r);
};

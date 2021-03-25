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
use Zhanhd\ReqRes\PointsRace\Tarlist\Request,
    Zhanhd\ReqRes\PointsRace\Tarlist\Response,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\Object\PointsRace,
    Zhanhd\Object\PointsRace\Daily as PointsRaceDaily,
    Zhanhd\Object\PointsRace\Target as PointsRaceTarget,
    Zhanhd\Extension\PointsRace     as PointsRaceModule;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $m = new PointsRaceModule($this);
    $race = new PointsRace;
    if (false === $race->find($m->cycle, $p->id)) {
        $race->cycle = $m->cycle;
        $race->pid = $p->id;
    }

    $raceDaily = new PointsRaceDaily;
    if (false === $raceDaily->find($m->cycle, $m->cday, $p->id)) {
        $raceDaily->cycle = $m->cycle;
        $raceDaily->cday  = $m->cday;
        $raceDaily->pid   = $p->id;
    }

    $flag = $request->flag->intval();
    $pay = $flag && $raceDaily->refreshed > 9;
    if ($pay && $p->gold < 20) {
        return $c->addReply($this->errorResponse->error('notenough diamond'));
    }

    if ($flag == 0 && $race->listTotal > 0) {
        $targets = $race->getTargets();
    } else {
        $race->dropTargets();
        $list = $m->genlist($p->id);
        $targets = $race->genTargets($list);
    }

    if ($flag) {
        $raceDaily->refreshed++;
        $raceDaily->save();
    }

    if ($pay) {
        $p->gold -= 20;
        $p->save();

        $r = new ResourceResponse;
        $r->retval->fromOwnerObject($p);
        $c->addReply($r);
    }

    $r = new Response;
    $r->targets->resize($targets->count());
    foreach ($r->targets as $i => $o) {
        $target = $targets->get($i);
        $o->fromObject($target, $m->score($target->tid));
    }
    $c->addReply($r);
};

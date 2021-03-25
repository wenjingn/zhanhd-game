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
use Zhanhd\ReqRes\WorldBoss\Gateway\Request,
    Zhanhd\ReqRes\WorldBoss\Gateway\Response,
    Zhanhd\Extension\WorldBoss\Module as WorldBossModule;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $m = new WorldBossModule($this);
    $flag = $request->flag->intval();
    if ($flag == Request::FLAG_QUERY) {
        $r = new Response;
        $st = $m->getStatus();
        $bi = $m->getBossInfo();
        $pi = $m->getPlayerInfo($p->id);
        $r->flag->intval($st->status);
        $r->id->intval($bi->id);
        $r->rank->intval($pi->rank);
        $r->damage->intval($pi->damage);
        $r->bosschp->intval($bi->chp<0?0:$bi->chp);
        $r->bossrhp->intval($bi->rhp);
        $r->leftsec->intval($st->leftsec);
        $c->addReply($r);
        return;
    }
    if (false === $m->checkTime()) {
        //return $c->addReply($this->errorResponse->error('invalid worldboss time'));
    }

    if ($flag == Request::FLAG_ENTER) {
        $m->enter($p->id);
    } else {
        $m->quit($p->id);
    }
};

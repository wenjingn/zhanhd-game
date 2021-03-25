<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Swoole\Client,
    System\Stdlib\Object;

/**
 *
 */
use Zhanhd\ReqRes\FormationResponse,
    Zhanhd\ReqRes\FormationInfo as Request,
    Zhanhd\Config\Store,
    Zhanhd\Extension\Achievement\Module as AchievementModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;

    if (null === ($pl = $p->getLineups('gid')->get($request->gid->intval()))) {
        return $c->addReply($this->errorResponse->error('invalid lineup'));
    }

    if (($fid = $request->fid->intval()) == 0) {
        goto sendResponse;
    }

    if (null === ($f = Store::get('formation', $fid))) {
        return $c->addReply($this->errorResponse->error('invalid formation'));
    }

    $intsum = $pl->heros->reduce(function($i, $o) {
        if ($o->peid) {
            return $i + (integer)($o->pe->property->int / 100);
        }

        return $i;
    }, 0);

    /* validate intsum */
    if ($intsum < $f->intreq) {
        return $c->addReply($this->errorResponse->error('int not enough'));
    }

    $pl->fid = $f->id;
    $pl->save();

    // trigger achievement event
    (new AchievementModule($p))->trigger((new Object)->import(array(
        'cmd'    => 'lineup',
        'strval' => 'formation',
    )));

    // sending resource response
    sendResponse: {
        $r = new FormationResponse;
        $r->formation->gid->intval($pl->gid);
        $r->formation->fid->intval($fid);
        $c->addReply($r);
    }
};

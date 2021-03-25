<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Swoole\Client,
    System\ReqRes\Int\U16 as Request;

/**
 *
 */
use Zhanhd\ReqRes\Unlock\Response,
    Zhanhd\Extension\Unlock,
    Zhanhd\Extension\PvpRank\Module as PvpRankModule;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $unlockId = $request->intval();
    if (false === isset(Unlock::$tab, $unlockId)) {
        return $c->addReply($this->errorResponse->error('invalid argvs'));
    }

    $k = sprintf('unlocked-%d', $unlockId);
    if ($p->profile->$k) {
        return $c->addReply($this->errorResponse->error('deprecated'));
    }

    $info = Unlock::$tab[$unlockId];
    $key = sprintf('currtask%d', $info[0]);
    $taskId = $info[1];
    if ($p->profile->$key < $taskId) {
        return $c->addReply($this->errorResponse->error('locked instance'));
    }

    if ($unlockId == Unlock::PVPUNLOCK_ID) {
        /* initial pvp rank */
        (new PvpRankModule)->push($p);
    }

    $p->profile->$k = 1;
    $p->profile->save();
    $r = new Response;
    $r->unlockId->intval($unlockId);
    $c->addReply($r);
};

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
use Zhanhd\ReqRes\Crusade\Attack\Request,
    Zhanhd\ReqRes\Crusade\Attack\Response,
    Zhanhd\Config\Store,
    Zhanhd\Object\Player\Lineup     as PlayerLineup,
    Zhanhd\Object\Player\Crusade    as PlayerCrusade,
    Zhanhd\Extension\Check\Module   as CheckModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p   = $c->local->player;
    $gid = $request->gid->intval();
    $cid = $request->cid->intval();

    if ($gid == 1 || false === isset(PlayerLineup::$groups[$gid])) {
        return $c->addReply($this->errorResponse->error('invalid lineup'));
    } else if (false === $p->getLineups('gid')->get($gid)->getCaptain()) {
        return $c->addReply($this->errorResponse->error('captain cannot be empty'));
    }

    // current player-crusade record
    $cpc = null;

    // finish others first
    foreach ($p->getCrusades() as $pc) {
        if ($pc->cid == $cid) {
            $cpc = $pc;
        }

        if ($pc->flags == PlayerCrusade::FLAG_DONE) {
            continue;
        }

        if ($pc->gid == $gid) {
            return $c->addReply($this->errorResponse->error('lineup is attacking'));
        }

        if ($pc->cid == $cid) {
            return $c->addReply($this->errorResponse->error('player-crusade not finish'));
        }
    }

    if (null === ($crusade = Store::get('crusade', $cid))) {
        return $c->addReply($this->errorResponse->error('crusade not found'));
    }

    $did = (integer)($p->profile->currtask1 / 10000);
    if ($did < $crusade->act) {
        return $c->addReply($this->errorResponse->error('crusade unlocked'));
    }

    if (false === CheckModule::packageAspect($c, $this)) {
        return;
    }

    if ($cpc === null) {
        $cpc = new PlayerCrusade;
        $cpc->pid     = $p->id;
        $cpc->cid     = $cid;
        $cpc->created = $this->ustime;

        $cpc->crusade = $crusade;
    }

    $cpc->gid     = $gid;
    $cpc->flags   = PlayerCrusade::FLAG_ATTACKING;
    $cpc->updated = $this->ustime;
    $cpc->save();

    $r = new Response;
    $r->crusade->fromPlayerCrusadeObject($cpc);
    $c->addReply($r);
};

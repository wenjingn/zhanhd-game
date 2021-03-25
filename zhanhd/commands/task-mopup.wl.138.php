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
use Zhanhd\ReqRes\Task\Mopup\Request,
    Zhanhd\ReqRes\Task\Mopup\Response,
    Zhanhd\Config\Store,
    Zhanhd\Extension\Hero\Module   as HeroModule,
    Zhanhd\Extension\Check\Module  as CheckModule,
    Zhanhd\Extension\Reward\Module as RewardModule;

/**
 *
 */
define('MOPUP_TICKET_ID', 410223);

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }
    
    $p = $c->local->player;
    if (null === ($battle = Store::get('battle', $request->getBattleId()))) {
        return $c->addReply($this->errorResponse->error('invalid battle id'));
    }

    $diff = $request->diff->intval();
    if (null === ($bdiff = $battle->getDiff($diff))) {
        return $c->addReply($this->errorResponse->error('notfound'));
    }

    $k = sprintf('currtask%d', $diff);
    if ((int)($p->profile->$k/100) <= $battle->id) {
        switch ($diff) {
        case 1:
            $error = 'lock average task';
            break;
        case 2:
            $error = 'lock elite task';
            break;
        case 3:
            $error = 'lock hell task';
            break;
        }
        return $c->addReply($this->errorResponse->error($error));
    }

    if ($p->profile->{MOPUP_TICKET_ID} < 1) {
        return $c->addReply($this->errorResponse->error('notenough mopup-ticket'));
    }

    $l = $p->getLineups('gid')->get($request->gid->intval());
    if (null === $l) {
        return $c->addReply($this->errorResponse->error('invalid lineup'));
    }
    if (false === $l->getCaptain()) {
        return $c->addReply($this->errorResponse->error('empty captain'));
    }

    if ($l->getLvlsum() < $bdiff->power) {
        return $c->addReply($this->errorResponse->error('lowlimit power'));
    }

    foreach ($l->heros as $plh) {
        if ($plh->peid == 0) continue;
        if ($plh->pe->property->getEnergy($this->ustime) < 25) {
            return $c->addReply($this->errorResponse->error('notenough energy'));
        }
    }

    if (false === CheckModule::packageAspect($c, $this)) {
        return;
    }

    $p->profile->{MOPUP_TICKET_ID}--;
    $p->profile->save();

    $exp = $bdiff->getExp($p->isMember());
    HeroModule::upgradeAspect($c, $this, $l, $exp, 25);
    
    $r = new Response;
    $r->num->intval($p->profile->{MOPUP_TICKET_ID});
    $r->exp->intval($exp);
    RewardModule::aspect($p, $bdiff->drop(), $r->reward, $c, $this);
    $c->addReply($r);
};

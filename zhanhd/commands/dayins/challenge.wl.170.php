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
use Zhanhd\Config\Store,
    Zhanhd\ReqRes\DayIns\Request,
    Zhanhd\ReqRes\DayIns\Response,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\Extension\Hero\Module   as HeroModule,
    Zhanhd\Extension\Reward\Module as RewardModule,
    Zhanhd\Extension\Combat\Module as CombatModule;

define("CONFIG_DAYINS_CONSUME_ENERGY", 50);

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    /* lineup check */
    if (null === ($pl = $p->getLineups('gid')->get($request->gid->intval()))) {
        return $c->addReply($this->errorResponse->error('invalid lineup'));
    }   
    
    if (false === $pl->getCaptain()) {
        return $c->addReply($this->errorResponse->error('empty captain'));
    }

    if (false === $pl->checkEnergy(CONFIG_DAYINS_CONSUME_ENERGY)) {
        return $c->addReply($this->errorResponse->error('energy not enough'));
    }

    /* day check */
    if (null === ($dayins = Store::get('dayins', $request->iid->intval()))) {
        return $c->addReply($this->errorResponse->error('notfound actins'));
    }

    if ($p->profile->currtask2 <= $dayins->unlock) {
        return $c->addReply($this->errorResponse->error('lock elite task'));
    }

    if (false === ($diff = $dayins->getDiff($request->diff->intval()))) {
        return $c->addReply($this->errorResponse->error('invalid dayins diff'));
    }

    if ($pl->getLvlSum() < $diff->rlvl) {
        return $c->addReply($this->errorResponse->error('low-limit dayins lvl'));
    }

    if ($p->counterCycle->getDayInsTimes() < 1) {
        return $c->addReply($this->errorResponse->error('limit dayins times'));
    }

    HeroModule::upgradeAspect($c, $this, $pl, 0, CONFIG_DAYINS_CONSUME_ENERGY);

    $r = new Response;
    $r->iid->intval($dayins->id);
    $r->diff->intval($diff->diff);
    (new CombatModule)->combat($pl, $diff->getNpcLineup(), $r->combat);
    if ($r->combat->win->intval()) {
        $p->counterCycle->dayins++;
        $p->counterCycle->save();

        foreach ($diff->drop() as $eid => $num) {
            $p->profile->$eid += $num;
            $p->profile->save();
            $r->eid->intval($eid);
            $r->num->intval($num);
            break;
        }
    }
    $r->times->intval($p->counterCycle->getDayInsTimes());
    $c->addReply($r);

    $r = new ResourceResponse;
    $r->retval->fromOwnerObject($p);
    $c->addReply($r);
};

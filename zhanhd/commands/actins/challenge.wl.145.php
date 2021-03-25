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
use Zhanhd\ReqRes\ActIns\Request,
    Zhanhd\ReqRes\ActIns\Response,
    Zhanhd\ReqRes\ActIns\UpdateResponse,
    Zhanhd\ReqRes\SysMsgResponse,
    Zhanhd\Config\Store,
    Zhanhd\Config\ActIns,
    Zhanhd\Extension\ActIns\Module as ActInsModule,
    Zhanhd\Extension\Combat\Module as CombatModule,
    Zhanhd\Extension\Reward\Module as RewardModule,
    Zhanhd\Extension\Hero\Module   as HeroModule;

/**
 *
 */
return function(Client $c) {
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

    if (false === $pl->checkEnergy(10)) {
        return $c->addReply($this->errorResponse->error('energy not enough'));
    }

    /* actins check */
    if (null === ($actins = Store::get('actins', $request->aid->intval()))) {
        return $c->addReply($this->errorResponse->error('notfound actins'));
    }

    $actinsInWeek = ActInsModule::fetch($this->redis, $this->week);
    if (false === in_array($actins->id, $actinsInWeek)) {
        $r = new UpdateResponse;
        $r->actins->resize(count($actinsInWeek));
        foreach ($r->actins as $i => $o) {
            $o->aid->intval($actinsInWeek[$i]);
        }
        $c->addReply($this->errorResponse->error('notfound actins'));
        return $c->addReply($r);
    }

    $errno = $actins->check($pl);
    if ($errno) {
        switch ($errno) {
            case ActIns::ERROR_NPCNUM:
                return $c->addReply($this->errorResponse->error('invalid actins npcnum'));
            case ActIns::ERROR_ARMYTP:
                return $c->addReply($this->errorResponse->error('invalid actins army-type'));
            case ActIns::ERROR_RARITY:
                return $c->addReply($this->errorResponse->error('invalid actins rarity'));
        }
    }

    /* floor check */
    if (false === ($floor = $actins->getFloor($request->floor->intval()))) {
        return $c->addReply($this->errorResponse->error('notfound floor'));
    }

    $ckey = $actins->getCounterKey();
    if ($floor->fid != $p->counterWeekly->$ckey + 1) {
        return $c->addReply($this->errorResponse->error('invalid actins sequence'));
    }

    HeroModule::upgradeAspect($c, $this, $pl, 0, 10);

    $r = new Response;
    $r->aid->intval($actins->id);
    $r->floor->intval($floor->fid);
    (new CombatModule)->combat($pl, $floor->getNpcLineup(), $r->combat);
    if ($r->combat->win->intval()) {
        $p->counterCycle->actins++;
        $p->counter->actins++;
        $p->counterCycle->save();
        $p->counter->save();

        $p->counterWeekly->$ckey = $floor->fid;
        $p->counterWeekly->save();
        ActInsModule::incrScore($this->redis, $this->week, $p->id, $this->ustime);
        RewardModule::aspect($p, $floor->drop(), $r->reward, $c, $this);

        /* broadcast */
        if ($floor->fid == 10 || $floor->fid == 20) {
            $smr = new SysMsgResponse;
            $smr->format(6, 1, array($p->name, $actins->name, $floor->fid));
            $this->task('broadcast-global', $smr);
        }
    }
    $c->addReply($r);
};

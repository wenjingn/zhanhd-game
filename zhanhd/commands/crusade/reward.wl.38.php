<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Stdlib\Object,
    System\Swoole\Client;

/**
 *
 */
use Zhanhd\ReqRes\Crusade\Reward\Request,
    Zhanhd\ReqRes\Crusade\Reward\Response,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\Extension\Achievement\Module as AchievementModule,
    Zhanhd\Extension\Hero\Module        as HeroModule,
    Zhanhd\Config\Entity                as SourceEntity,
    Zhanhd\Config\WeekMission,
    Zhanhd\Object\Player\Crusade        as PlayerCrusade,
    Zhanhd\Extension\Reward\Module      as RewardModule,
    Zhanhd\Extension\WeekMission\Module as WeekMissionModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p  = $c->local->player;
    $pc = new PlayerCrusade;

    if (false === $pc->find($p->id, $request->cid->intval())) {
        return $c->addReply($this->errorResponse->error('player-crusade not found'));
    }

    $pc->autoStatus();
    if ($pc->flags <> PlayerCrusade::FLAG_ACCEPTING) {
        return $c->addReply($this->errorResponse->error('player-crusade not acceptable'));
    }

    $r = new Response;
    $r->cid->intval($pc->cid);
    $r->aid->intval($pc->crusade->act);
    $r->seq->intval($pc->crusade->seq);
    $r->gid->intval($pc->gid);

    if (($items = $pc->getRewards($p))) {
        $r->win->intval(1);

        if ($exp = $pc->crusade->getExp($p->isMember())) {

            HeroModule::upgradeAspect($c, $this, $p->getLineups('gid')->get($pc->gid), $exp, false);
            $r->exp->intval($exp);
        }

        $rewards = [];
        foreach ($items as $i => $o) {
            $rewards[$o->e->id] = $o->n;
        }
        RewardModule::aspect($p, $rewards, $r->reward, $c, $this);

        $pc->times++;
    }

    $pc->flags   = PlayerCrusade::FLAG_DONE;
    $pc->updated = $this->ustime;
    $pc->save();

    // trigger achievement event
    $p->counter->crusade++;
    $p->counter->save();

    $p->counterWeekly->crusade++;
    $p->counterWeekly->save();
    WeekMissionModule::trigger($p, $this, WeekMission::TYPE_CRUSADE, $p->counterWeekly->crusade);

    $c->addReply($r);
};

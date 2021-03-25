<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Account;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set,
    System\ReqRes\Str,
    System\ReqRes\Int\U08,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\ReqRes\Account\UserInfo,
    Zhanhd\ReqRes\LeaderInfo,
    Zhanhd\ReqRes\CrusadeInfo,
    Zhanhd\ReqRes\Lineup\Equip,
    Zhanhd\ReqRes\Lineup\Hero\Request as LineupPackage,
    Zhanhd\ReqRes\Entity\Hero,
    Zhanhd\ReqRes\Entity\Prop,
    Zhanhd\ReqRes\Entity\Soul,
    Zhanhd\ReqRes\Entity\Entity,
    Zhanhd\ReqRes\Building\Building,
    Zhanhd\ReqRes\TaskInfo,
    Zhanhd\ReqRes\ResourceInfo,
    Zhanhd\ReqRes\ActIns\Info           as ActInsInfo,
    Zhanhd\ReqRes\ActivityMetaInfo,
    Zhanhd\ReqRes\FriendInfo,
    Zhanhd\ReqRes\EnergyConfigInfo,
    Zhanhd\ReqRes\AchievementInfo,
    Zhanhd\ReqRes\NewzoneMission\Info   as NewzoneMissionInfo,
    Zhanhd\ReqRes\WeekMission\Info      as WeekMissionInfo,
    Zhanhd\ReqRes\QuestionInfo,
    Zhanhd\ReqRes\Store\FriendShipInfo,
    Zhanhd\ReqRes\Hero\Refine\Info      as HeroRefineInfo,
    Zhanhd\ReqRes\Guild\Info            as GuildInfo,
    Zhanhd\Config\Store,
    Zhanhd\Config\Instance,
    Zhanhd\Config\Entity                as SourceEntity,
    Zhanhd\Object\User,
    Zhanhd\Object\Player                as Owner,
    Zhanhd\Object\Player\Relation       as PlayerRelation,
    Zhanhd\Object\Player\Entity         as PlayerEntity,
    Zhanhd\Object\Player\Entity\Refine  as PlayerEntityRefine,
    Zhanhd\Object\Player\Lineup         as PlayerLineup,
    Zhanhd\Object\Player\Crusade        as PlayerCrusade,
    Zhanhd\Object\Player\Lineup\Equip   as PlayerLineupEquip,
    Zhanhd\Object\Player\Counter\Cycle  as PlayerCounterCycle,
    Zhanhd\Object\Player\Daily\Question as PlayerDailyQuestion,
    Zhanhd\Object\ActivityPlan,
    Zhanhd\Object\Player\Coherence      as PlayerCoherence,
    Zhanhd\Extension\PvpRank\Module     as PvpRankModule,
    Zhanhd\Extension\Question\Module    as QuestionModule,
    Zhanhd\Extension\ActIns\Module      as ActInsModule,
    Zhanhd\Extension\WeekMission\Module as WeekMissionModule,

    Zhanhd\ReqRes\RechargeReward\Status as RechargeRewardStatus,
    Zhanhd\ReqRes\FixedTimeReward\Status as FixedTimeRewardStatus,

    Zhanhd\ReqRes\Act\Info               as ActInfo;

/**
 *
 */
class InitResponse extends ReqResHeader
{
    /**
     * @param User   $u
     * @param Owner  $o
     * @param Object $globals
     * @return void
     */
    public function fromObject(User $u, Owner $o, $globals)
    {
        $this->user->fromUserObject($u);
        $this->user->id->intval($o->id);
        $this->leader->fromPlayerObject($o);

        // buildings
        $pbs = $o->getBuildings();
        $this->buildings->resize($pbs->count());
        foreach ($this->buildings as $i => $b) {
            $b->fromPlayerBuildingObject($pbs->get($i));
        }

        // heros & items & equips
        $pes = $o->getEntities();

        // heros
        $i = 0;
        $x = $pes->filter(function($pe) {
            return (boolean) ($pe->e->type == SourceEntity::TYPE_HERO);
        });

        $wives = [];
        $this->heros->resize($x->count());
        foreach ($x as $pe) {
            $this->heros->get($i++)->fromPlayerEntityObject($pe, $globals);
            if ($pe->property->married) {
                $wives[] = $pe->id;
            }
        }

        // items
        // $i = 0;
        // $x = $pes->filter(function($pe) {
        //     switch ($pe->e->type) {
        //     case SourceEntity::TYPE_WEAPON:
        //     case SourceEntity::TYPE_ARMOR:
        //     case SourceEntity::TYPE_HORSE:
        //     case SourceEntity::TYPE_JEWEL:
        //     case SourceEntity::TYPE_RING:
        //         return true;
        //     }

        //     return false;
        // });

        // $this->items->resize($x->count());
        // foreach ($x as $pe) {
        //     $this->items->get($i)->forge->intval($pe->property->forge);
        //     $this->items->get($i)->peid ->intval($pe->id);
        //     $this->items->get($i)->eid  ->intval($pe->eid);
        //     $i++;
        // }

        // equips
        // $i = 0;
        // $x = [];
        $lineups = $o->getLineups();
        // foreach ($lineups as $pl) {
        //     foreach ($pl->heros as $plh) {
        //         foreach ($plh->equips->getIterator()->filter(function($peid) {
        //             return $peid;
        //         }) as $use => $peid) {
        //             $x[$peid] = [$plh->gid, $plh->pos, $use];
        //         }
        //     }
        // }

        // $this->equips->resize(count($x));
        // foreach ($x as $peid => list($gid, $pos, $use)) {
        //     $this->equips->get($i)->gid->intval($gid);
        //     $this->equips->get($i)->pos->intval($pos);
        //     $this->equips->get($i)->use->intval($use);
        //     $this->equips->get($i)->pe->peid->intval($pes->$peid->id);
        //     $this->equips->get($i)->pe->eid ->intval($pes->$peid->eid);
        //     $i++;
        // }

        // crusades
        $pcs = [
            2 => 0,
            3 => 0,
            4 => 0,
        ];

        $o->getCrusades()->filter(function($o) {
            return (boolean) ($o->flags <> PlayerCrusade::FLAG_DONE);
        })->map(function($o) use (&$pcs) {
            $o->autoStatus();
            switch ($o->flags) {
            case PlayerCrusade::FLAG_ATTACKING:
            case PlayerCrusade::FLAG_ACCEPTING:
                $pcs[$o->gid] = $o;
            }
        });

        $this->crusades->resize(3); $i = 0; foreach ($pcs as $gid => $x) {
            $pc = $this->crusades->get($i++);
            if ($x) {
                $pc->fromPlayerCrusadeObject($x);
            } else {
                $pc->gid ->intval($gid);
                $pc->flag->intval(1);
            }
        }

        // lineups
        $this->lineups->resize($lineups->count());
        foreach ($this->lineups as $i => $l) {
            $l->gid->intval($lineups->get($i+1)->gid);
            $l->fid->intval($lineups->get($i+1)->fid);

            $l->lineups->resize($lineups->get($i+1)->heros->count());
            foreach ($l->lineups as $j => $h) {
                $h->fromPlayerLineupHeroObject($lineups->get($i+1)->heros->get($j));
            }
        }

        // resources
        $this->resources->fromOwnerObject($o);

        // captain
        if (($captain = $lineups->map(null, function($x, $o) {
            return $o->gid;
        })->get(1)->getCaptain())) {
            $this->captain->intval($captain->peid);
        }

        // task
        foreach ([Instance::DIFF_NORMAL, Instance::DIFF_HARD, Instance::DIFF_CRAZY] as $diff) {
            $currtask = $o->profile->{'currtask'.$diff};
            $task = 'task'.$diff;
            $this->$task->flag->intval($diff);
            if (empty($currtask)) {
                $this->$task->setFightId(10000);
            } else {
                $ins = Store::get('ins'.$diff, $currtask);
                if (empty($ins->prev)) {
                    $this->$task->setFightId(10100);
                } else {
                    $this->$task->setFightId($ins->prev);
                }
            }
        }

        // pvp
        $this->pvprank->intval((new PvpRankModule)->rank($o));
        $this->pvpnum ->intval(PlayerCounterCycle::DAILY_PVP_LIMIT - $o->counterCycle->pvp);
        $this->pvplmt ->intval(PlayerCounterCycle::DAILY_PVP_LIMIT);

        // achievement
        $as = Store::get('achievement'); $i = 0; $this->achievements->resize(count($as)); foreach ($as as $a) {
            $this->achievements->get($i++)->fromOwnerObject($a, $o);
        }

        /* newzone mission */
        $missions = Store::get('newzoneMission'); $i = 0; $this->newzoneMissions->resize(count($missions)); foreach ($missions as $m) {
            $this->newzoneMissions->get($i++)->fromObject($m, $o);
        }

        /* week mission */
        WeekMissionModule::syncWeek($globals);
        $i = 0;
        foreach (WeekMissionModule::$weekTypes as $type => $ignore) {
            $weekMissions = Store::get('weekMissionIndexByType', $type);
            $this->weekMissions->append(count($weekMissions));
            foreach ($weekMissions as $m) {
                $this->weekMissions->get($i++)->fromObject($m, $o, $globals);
            }
        }

        /* props souls */
        $props = [];
        $souls = [];
        foreach (Store::get('entity') as $e) {
            $x = $o->profile->{$e->id};
            if (empty($x)) continue;
            if ($e->isProp()) {
                $props[$e->id] = $x;
            } else if ($e->type == SourceEntity::TYPE_SOUL) {
                $souls[$e->id] = $x;
            }
        }
        if (($n = count($props))) {
            $i = 0; $this->props->resize($n); foreach ($props as $eid => $num) {
                $this->props->get($i)->eid->intval($eid);
                $this->props->get($i)->num->intval($num);
                $i++;
            }
        }
        if ($n = count($souls)) {
            $i = 0; $this->souls->resize($n); foreach ($souls as $eid => $num) {
                $this->souls->get($i)->eid->intval($eid);
                $this->souls->get($i)->num->intval($num);
                $i++;
            }
        }

        /**
         * @wives
         * @variable $wives from hero traverse
         */
        $this->wives->resize(count($wives));
        foreach ($wives as $k => $peid) {
            $this->wives->get($k)->intval($peid);
        }
        $this->marryLimit->intval(PlayerCounterCycle::DAILY_MARRY_LIMIT);
        $this->marryTimes->intval($o->counterCycle->marry);

        /* actins */
        $actins = ActInsModule::fetch($globals->redis, $globals->week);
        $this->actins->resize(count($actins));
        foreach ($this->actins as $i => $obj) {
            $obj->aid->intval($actins[$i]);
            $obj->floor->intval($o->counterWeekly->{'actins-'.$actins[$i]});
        }

        /* blacklist */
        $blacklist = $o->getBlacklist();
        $this->blacklist->resize($blacklist->count());
        $i = 0;
        foreach ($blacklist as $black) {
            $this->blacklist->get($i)->fromRelationObject($black, $globals);
            $i++;
        }

        $this->guideFlag->intval($o->profile->guideFlag);
        $this->guideId->intval($o->profile->guideId);

        /* questions */
        $questions = QuestionModule::fetch($globals->redis, $globals->date);
        $this->questions->resize(count($questions));
        foreach ($this->questions as $i => $obj) {
            $obj->intval($questions[$i]);
        }

        $this->questionInfo->fromPlayerObject($o);
        $this->vquestion->intval($globals->date);

        $remainTime = $o->recent->freeRecruit2030 + 48 * 3600 * 1000000 - $globals->ustime;
        $this->recruitFreeCD->intval($remainTime > 0 ? (integer) ($remainTime / 1000000) : 0);

        $this->memcardRemain->intval($o->memcardRemain());
        // $this->equipPackageCapacity->intval($o->profile->equipPackageCapacity);
        $this->heroPackageCapacity->intval($o->profile->heroPackageCapacity);

        $this->talentShowLimit->intval(PlayerCounterCycle::DAILY_TALENT_LIMIT);
        $this->talentShowTimes->intval($o->counterCycle->talent);
        $this->energyShopTimes->intval($o->counterCycle->{Store::get('goods', 204)->getCounterKey()});

        $this->friendShip->intval((int)PlayerCoherence::get($globals->pdo, $o->id, 'friendship'));
        $this->timestamp->intval((int)($globals->ustime/1000000));

        /* signin days */
        $this->signinWDays->intval($o->counter->greenerSignin);
		$this->signinWDays->bitset((boolean)$o->counterCycle->greenerSignin << 3);
        $this->signinMDays->intval($o->counterMonthly->sign);
        $this->signinMDays->bitset((boolean)$o->counterCycle->sign << 5);

        /* first double merchandises */
        $merchandises = [];
        foreach (Store::get('merchandise') as $m) {
            if ($m->id == 101) {
                continue;
            }

            $ckey = $m->getCounterKey();
            if ($o->counter->$ckey > 0) {
                continue;
            }
            $merchandises[] = $m;
        }
        $this->firstDoubles->resize(count($merchandises));
        foreach ($this->firstDoubles as $i => $f) {
            $f->intval($merchandises[$i]->id);
        }

        /* accumulate deposit */
        $this->hasRecharged->intval($o->deposit ? 1 : 0);
        $acceptings = [];
        foreach (Store::get('deposit') as $d) {
            $ckey = $d->getCounterKey();
            if ($d->id == 1 && $o->counter->$ckey) {
                $this->hasRechargedAccepted->intval(1);
            }
            if ($o->counter->$ckey < 1) {
                $acceptings[] = $d;
            }
        }
        $this->accumDepositRewards->resize(count($acceptings));
        foreach ($this->accumDepositRewards as $i => $obj) {
            $obj->intval($acceptings[$i]->id);
        }
        $this->accumDeposit->intval($o->deposit);

        /* invite */
        $this->beInvited->intval($o->isInvited());
        $this->invcode->strval($o->invcode);
        $this->invcount->intval((int)PlayerCoherence::get($globals->pdo, $o->id, 'invcount'));
        $acceptings = [];
        foreach (Store::get('invite') as $obj) {
            $ckey = $obj->getCounterKey();
            if ($o->counter->$ckey < 1) {
                $acceptings[] = $obj;
            }
        }
        $this->invRewards->resize(count($acceptings));
        foreach ($this->invRewards as $i => $obj) {
            $obj->intval($acceptings[$i]->id);
        }

        /* friend ship store */
        $this->friendShipStore->fromGlobalObject($globals);

        /* hero refines */
        $refines = $o->getRefine();
        $this->refines->resize($refines->count());
        $i = 0;
        foreach ($refines as $refine) {
            $this->refines->get($i)->fromRefineObject($refine);
            $i++;
        }

        /* open zone n days */
        $this->openZoneNDays->intval($globals->getDayFromZoneOpen());
        $left = $globals->zoneOpenTime + 604800 - $globals->time;
        $this->newzoneMissionLeftTime->intval($left > 0 ? (int)$left: 0);
        $this->weekMissionLeftTime->intval(604800-$globals->epoch%604800);
        $this->dayinsRemainTimes->intval($o->counterCycle->getDayInsTimes());
        $this->wbRebornTimes->intval($o->counterCycle->wbRebornTimes);

        /* guild info */
        if ($guildMember = $o->getGuildMember()) {
            $guild = $guildMember->getGuild();
            $this->guild->fromObject($guild, $guildMember, $globals);
        }

        /* recharge-reward status */
        $status = Store::get('rechargeReward');
        $this->rechargeRewardStatus->resize(count($status));
        $i = 0;
        foreach ($status as $x) {
            $m = Store::get('merchandise', $x->id);
            $r = $m->getRechargeRewardKey();
            $a = $m->getRechargeRewardAcceptedKey();
            $this->rechargeRewardStatus->get($i)->id->intval($x->id);
            $this->rechargeRewardStatus->get($i)->r ->intval($o->counterCycle->$r);
            $this->rechargeRewardStatus->get($i)->a ->intval($o->counterCycle->$a);
            $i++;
        }

        /* fixed-time-reward status */
        $status = Store::get('fixedTimeReward');
        $this->fixedTimeRewardStatus->resize(count($status));
        $i = 0;
        $flag = true;
        foreach ($status as $x) {
            $k = $x->getCounterKey();

            if (empty($o->counterCycle->$k) && $flag) {
                if ($x->id == 1) {
                    $this->fixedTimeRewardCD->intval(max(0, $x->sec - $o->counterCycle->onlineDur));
                } else {
                    $this->fixedTimeRewardCD->intval($o->counterCycle->nextOnlineRewardAccepted);
                }

                $this->fixedTimeRewardId->intval($x->id);
                $flag = false;
            }

            $this->fixedTimeRewardStatus->get($i)->id  ->intval($x->id);
            $this->fixedTimeRewardStatus->get($i)->flag->intval($o->counterCycle->$k ? 1 : 0);
            $i++;
        }

        /* @see fixed-time-prop-cmd */
        $k1 = sprintf('fixed-time-prop-%d', 12);
        $k2 = sprintf('fixed-time-prop-%d', 18);
        $k3 = sprintf('fixed-time-prop-%d', 21);
        $this->fixedTimeProp1->intval($o->counterCycle->$k1 ? 1 : 0);
        $this->fixedTimeProp2->intval($o->counterCycle->$k2 ? 1 : 0);
        $this->fixedTimeProp3->intval($o->counterCycle->$k3 ? 1 : 0);

        $this->recruitHalfGold->intval($o->counterCycle->{Store::get('goods', 2031)->getCounterKey()} < 1);

        $plans = ActivityPlan::getsFresh($globals->pdo, $globals->ustime/1000000);
        $this->acts->resize($plans->count());
        foreach ($this->acts as $i => $act) {
            $plan = $plans->get($i);
            $act->type->intval($plan->type);
            $act->begin->intval($plan->begin);
            $act->end->intval($plan->end);
        }
		$this->expCardShopTimes->intval($o->counterCycle->{Store::get('propGoods', 246)->getCounterKey()});

        $unlockIds = range(1, 14);
        foreach ($unlockIds as $i => $id) {
            $k = sprintf('unlocked-%d', $id);
            if (!$o->profile->$k) {
                unset($unlockIds[$i]);
            }
        }
        $this->unlockIds->resize(count($unlockIds));
        $i = 0;
        foreach ($unlockIds as $id) {
            $this->unlockIds->get($i)->intval($id);
            $i++;
        }
    }

    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(9);

        $this->attach('user',           new UserInfo);
        $this->attach('leader',         new LeaderInfo);
        $this->attach('buildings',      new Set(new Building));
        $this->attach('heros',          new Set(new Hero));
        // $this->attach('items',          new Set(new Entity));
        $this->attach('props',          new Set(new Prop));
        $this->attach('souls',          new Set(new Soul));
        $this->attach('equips',         new Set(new Equip));
        $this->attach('crusades',       new Set(new CrusadeInfo));
        $this->attach('lineups',        new Set(new LineupPackage));
        $this->attach('achievements',   new Set(new AchievementInfo));
        $this->attach('newzoneMissions',new Set(new NewzoneMissionInfo));
        $this->attach('weekMissions',   new Set(new WeekMissionInfo));

        $this->attach('wives',          new Set(new U64));
        $this->attach('marryLimit',     new U32);
        $this->attach('marryTimes',     new U32);
        $this->attach('questions',      new Set(new U16));
        $this->attach('questionInfo',   new QuestionInfo);
        $this->attach('vquestion',      new U32);

        $this->attach('actins',         new Set(new ActInsInfo));
        $this->attach('actrec',         new Set(new ActivityMetaInfo));

        $this->attach('blacklist',      new Set(new FriendInfo));

        $this->attach('resources',      new ResourceInfo);
        $this->attach('captain',        new U64);
        $this->attach('task1',          new TaskInfo);
        $this->attach('task2',          new TaskInfo);
        $this->attach('task3',          new TaskInfo);
        $this->attach('pvplmt',         new U32);
        $this->attach('pvpnum',         new U32);
        $this->attach('pvprank',        new U32);
        $this->attach('energycfg',      new EnergyConfigInfo);
        $this->attach('guideFlag',      new U16);
        $this->attach('guideId',        new U16);
        $this->attach('recruitFreeCD',  new U32);
        $this->attach('memcardRemain',  new U32);
        // $this->attach('equipPackageCapacity', new U16);
        $this->attach('heroPackageCapacity',  new U16);
        $this->attach('talentShowLimit', new U16);
        $this->attach('talentShowTimes', new U16);
        $this->attach('energyShopTimes', new U16);
        $this->attach('CONFIG_FRIEND_MAX', new U32);
        $this->CONFIG_FRIEND_MAX->intval(PlayerRelation::MAX_LIMIT);
        $this->attach('friendShip',      new U16);
        $this->attach('CONFIG_FRIENDSHIP_MAX', new U16);
        $this->CONFIG_FRIENDSHIP_MAX->intval(PlayerCounterCycle::DAILY_FRIENDSHIP_LIMIT);
        $this->attach('timestamp',       new U32);
        $this->attach('signinWDays',     new U16);
        $this->attach('signinMDays',     new U16);
        $this->attach('firstDoubles',    new Set(new U32));
        $this->attach('accumDepositRewards', new Set(new U16));
        $this->attach('accumDeposit', new U32);
        $this->attach('beInvited',    new U08);
        $this->attach('invcode',      new Str);
        $this->attach('invcount',     new U32);
        $this->attach('invRewards',   new Set(new U16));
        $this->attach('friendShipStore', new FriendShipInfo);
        $this->attach('refines', new Set(new HeroRefineInfo));
        $this->attach('openZoneNDays', new U16);
        $this->attach('newzoneMissionLeftTime', new U32);
        $this->attach('weekMissionLeftTime', new U32);
        $this->attach('dayinsRemainTimes', new U16);
        $this->attach('wbRebornTimes',     new U16);
        $this->attach('guild',             new GuildInfo);

        $this->attach('rechargeRewardStatus',  new Set(new RechargeRewardStatus));
        $this->attach('fixedTimeRewardId',     new U16);
        $this->attach('fixedTimeRewardCD',     new U16);
        $this->attach('fixedTimeRewardStatus', new Set(new FixedTimeRewardStatus));

        $this->attach('fixedTimeProp1',     new U16);
        $this->attach('fixedTimeProp2',     new U16);
        $this->attach('fixedTimeProp3',     new U16);

        $this->attach('recruitHalfGold',    new U16);

        $this->attach('hasRecharged',         new U16);
        $this->attach('hasRechargedAccepted', new U16);
        $this->attach('acts', new Set(new ActInfo));
		$this->attach('expCardShopTimes', new U16);
        $this->attach('unlockIds', new Set(new U16));
    }
}

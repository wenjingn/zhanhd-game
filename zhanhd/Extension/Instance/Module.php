<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Instance;

/**
 *
 */
use System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Config\Instance,
    Zhanhd\Config\Instance\Event,
    Zhanhd\Config\WeekMission,
    Zhanhd\Config\NewzoneMission,
    Zhanhd\ReqRes\Task\FightEventResponse,
    Zhanhd\ReqRes\Task\ResourceEventResponse,
    Zhanhd\ReqRes\Task\RandomEventResponse,
    Zhanhd\ReqRes\Task\BranchEventResponse,
    Zhanhd\ReqRes\Task\UnlockTaskResponse,
    Zhanhd\Extension\Guide                 as GuideModule,
    Zhanhd\Extension\Hero\Module           as HeroModule,
    Zhanhd\Extension\Check\Module          as CheckModule,
    Zhanhd\Extension\Reward\Module         as RewardModule,
    Zhanhd\Extension\Combat\Module         as CombatModule,
    Zhanhd\Extension\Achievement\Module    as AchievementModule,
    Zhanhd\Extension\WeekMission\Module    as WeekMissionModule,
    Zhanhd\Extension\NewzoneMission\Module as NewzoneMissionModule;


/**
 *
 */
class Module
{
    /**
     * @const integer
     */
    const ERR_UNINIT = 0;
    const ERR_LOCKED = 1;

    /**
     * @param Instance $ins
     * @param Event    $evt
     * @return void
     */
    public function __construct($ins, $evt, $pl)
    {
        $this->ins = $ins;
        $this->evt = $evt;
        $this->pl  = $pl;
        $this->eckey = $ins->getEventCounterKey($evt->evt);
        $this->gonext = false;
    }

    /**
     * @param Client $c
     * @param Global $g
     */
    protected function instanceBefore($c, $g)
    {
        $key = 'currtask'.$this->ins->diff;
        $progress = $c->local->player->profile->$key;
        if ($progress < Instance::INIT) {
            $c->addReply($g->errorResponse->error('uninit instance'));
            return false;
        }

        if ($progress < $this->ins->id) {
            $c->addReply($g->errorResponse->error('locked instance'));
            return false;
        }

        foreach ($this->pl->heros as $plh) {
            if ($plh->peid == 0) continue;
            if ($plh->pe->property->getEnergy() < $this->ins->energy) {
                $c->addReply($g->errorResponse->error('notenough energy'));
                return false;
            }
        }

        if (false === CheckModule::packageAspect($c, $g)) {
            return false;
        }

        switch ($this->ins->diff) {
        case Instance::DIFF_NORMAL:
            $key = 'instanceNormal';
            break;
        case Instance::DIFF_HARD:
            $key = 'instanceHard';
            break;
        case Instance::DIFF_CRAZY:
            $key = 'instanceCrazy';
            break;
        }
        $c->local->player->counterCycle->$key++;
        $c->local->player->counter->$key++;
        $c->local->player->counterCycle->save();
        $c->local->player->counter->save();
        return true;
    }

    /**
     * @param Client  $c
     * @param Global  $g
     * @return void
     */
    protected function before($c, $g)
    {
        if ($this->evt->ishead()) {
            return $this->instanceBefore($c, $g);
        }
        
        if ($c->mixed->lastcmd->intval() != $g->currcmd->code) {
            $c->addReply($g->errorResponse->error('nothead instance-event'));
            return false;
        }
        if ($c->mixed->lasttask->getFightId() != $this->ins->id ||
            false === isset($this->evt->prev[$c->mixed->lasttask->eid->intval()]) ||
            $c->mixed->lasttask->flag->intval() != $this->ins->diff) {
            $c->addReply($this->errorResponse->error('invalid instance-event-chain'));
            return false;
        }
        return true;
    }

    /**
     * @static array
     */
    protected static $handlers = [
        Event::TYPE_FIGHT    => 'fightHandler',
        Event::TYPE_RESOURCE => 'resourceHandler',
        Event::TYPE_BRANCH   => 'branchHandler',
        Event::TYPE_RANDOM   => 'randomHandler',
    ];

    /**
     * @param Client  $c
     * @param Global  $g
     * @return void
     */
    public function process($c, $g)
    {
        if (false === $this->before($c, $g)) return;
        $handler = self::$handlers[$this->evt->getType()];
        $this->$handler($c, $g);
        $this->after($c, $g);
        HeroModule::upgradeAspect($c, $g, $this->pl, $this->evt->getExp($c->local->player->isMember()), $this->evt->ishead() ? $this->ins->energy : false);
    }

    /**
     * @param Client  $c
     * @param Global  $g
     * @return void
     */
    protected function after($c, $g)
    {
        if (false === $this->gonext) {
            $c->mixed->lasttask->setTaskId(0);
            $c->mixed->lasttask->flag->intval(0);
            return;
        }
        
        $c->local->player->counter->{$this->eckey}++;
        $c->local->player->counter->save();
        if ($this->evt->istail()) {
            $this->instanceAfter($c, $g);
        } else {
            $c->mixed->lasttask->setFightId($this->ins->id);
            $c->mixed->lasttask->eid->intval($this->evt->evt);
            $c->mixed->lasttask->flag->intval($this->ins->diff);
        }
    }

    /**
     * @param Client $c
     * @param Global $g
     * @return void
     */
    protected function instanceAfter($c, $g)
    {
        $c->mixed->lasttask->setTaskId(0);
        $c->mixed->lasttask->flag->intval(0);

        $key = 'currtask'.$this->ins->diff;
        if ($this->ins->id  == $c->local->player->profile->$key) {
            if (empty($this->ins->next)) {
                throw new Exception('no next task');
            }
            $c->local->player->profile->$key = $this->ins->next;
            $r = new UnlockTaskResponse;
            $r->unlock->setFightId($this->ins->id);
            $r->unlock->flag->intval($this->ins->diff);
            $c->addReply($r);
            if ($this->ins->id == Instance::UNLOCK) {
                if ($this->ins->diff == Instance::DIFF_NORMAL) {
                    if ($c->local->player->profile->currtask2 < Instance::INIT) {
                        $r = new UnlockTaskResponse;
                        $r->unlock->setFightId(10100);
                        $r->unlock->flag->intval(Instance::DIFF_HARD);
                        $c->addReply($r);
                        $c->local->player->profile->currtask2 = Instance::INIT;
                    }
                } else if ($this->ins->diff == Instance::DIFF_HARD) {
                    if ($c->local->player->profile->currtask3 < Instance::INIT) {
                        $r = new UnlockTaskResponse;
                        $r->unlock->setFightId(10100);
                        $r->unlock->flag->intval(Instance::DIFF_CRAZY);
                        $c->addReply($r);
                        $c->local->player->profile->currtask3 = Instance::INIT;
                    }
                }
            }
            $c->local->player->profile->save();
        }

        switch ($this->ins->id) {
        case 10101:
            GuideModule::aspect($c, 2);
            break;
        case 10102:
            GuideModule::aspect($c, 7);
            break;
        case 10103:
            GuideModule::aspect($c, 10);
            break;
        }

        NewzoneMissionModule::trigger($c->local->player, $g, NewzoneMission::TYPE_TASK, $this->ins->id);
        if ($this->ins->diff == Instance::DIFF_HARD) {
            $c->local->player->counterWeekly->hardins++;
            $c->local->player->counterWeekly->save();
            WeekMissionModule::trigger($c->local->player, $g, WeekMission::TYPE_HARDINS, $c->local->player->counterWeekly->hardins);
        } else if ($this->ins->diff == Instance::DIFF_CRAZY) {
            $c->local->player->counterWeekly->crazyins++;
            $c->local->player->counterWeekly->save();
            WeekMissionModule::trigger($c->local->player, $g, WeekMission::TYPE_CRAZYINS, $c->local->player->counterWeekly->crazyins);
        }

        (new AchievementModule($c->local->player))->trigger((new Object)->import([
            'cmd'    => 'task',
            'argv'   => $this->ins->id,
            'strval' => $this->ins->diff == Instance::DIFF_NORMAL ? 'normal' : 'elite',
        ]));
    }
    
    /**
     * @param Client $c
     * @Param Global $g
     * @return void
     */
    protected function fightHandler($c, $g)
    {
        $r = new FightEventResponse;
        $r->task->setFightId($this->ins->id);
        $r->task->eid->intval($this->evt->evt);
        $r->task->flag->intval($this->ins->diff);
        (new CombatModule)->combat($this->pl, $this->evt->getNpcLineup(), $r->combat);
        $this->gonext = (boolean) $r->combat->win->intval();
        if (false === $this->gonext) return $c->addReply($r);
        $r->exp->intval($this->evt->getExp($c->local->player->isMember()));
        RewardModule::aspect($c->local->player, $this->evt->drop($c->local->player->counter->{$this->eckey} < 1), $r->reward, $c, $g);
        $c->addReply($r);
    }

    /**
     * @param Client $c
     * @param Global $g
     * @return void
     */
    protected function resourceHandler($c, $g)
    {
        $this->gonext = true;
        $r = new ResourceEventResponse;
        RewardModule::aspect($c->local->player, $this->evt->drop($c->local->player->counter->{$this->eckey} < 1), $r->reward, $c, $g);
        $r->task->setFightId($this->ins->id);
        $r->task->eid->intval($this->evt->evt);
        $r->task->flag->intval($this->ins->diff);
        $c->addReply($r);
    }

    /**
     * @param Client $c
     * @param Global $g
     * @return void
     */
    protected function branchHandler($c, $g)
    {
        $this->gonext = true;
        $r = new BranchEventResponse;
        $r->task->setFightId($this->ins->id);
        $r->task->eid->intval($this->evt->evt);
        $r->task->flag->intval($this->ins->diff);
        $c->addReply($r);
    }

    /**
     * @param Client $c
     * @param Global $g
     * @return void
     */
    protected function randomHandler($c, $g)
    {
        $this->gonext = true;
        $r = new RandomEventResponse;
        $r->task->setFightId($this->ins->id);
        $r->task->eid->intval($this->evt->evt);
        $r->task->flag->intval($this->ins->diff);
        $r->rand->intval($this->evt->getRandom());
        $c->addReply($r);
    }
}

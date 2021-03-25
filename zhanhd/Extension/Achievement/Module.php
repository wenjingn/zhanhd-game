<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Achievement;

/**
 *
 */
use System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Config\Achievement           as SourceAchievement,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Achievement    as PlayerAchievement;

/**
 *
 */
class Module
{
    /**
     * @var Player
     */
    private $p = null;

    /**
     * @var Object
     */
    private $c = null;

    /**
     * @var string
     */
    private $k = null;

    /**
     * @var array
     */
    public static $notify = array();

    /**
     *
     * @param  Player $p
     * @param  Object $global
     * @return void
     */
    public function __construct(Player $p, $global = null)
    {
        $this->p = $p;
        $this->c = Store::get('achievement');
        $this->global = $global;
    }

    /**
     *
     * @param  Object $o
     * @return void
     */
    public function trigger(Object $o)
    {
        if (empty($o->cmd) || false === method_exists($this, $o->cmd)) {
            return;
        }

        foreach ($this->c as $a) {
            if ($a->cmd <> $o->cmd) {
                continue;
            } else if ($a->strval && $a->strval <> $o->strval) {
                continue;
            }

            $retval = call_user_func(array($this, $o->cmd), $a, $o->argv, $o->intval, $o->strval);
            if (is_array($retval)) {
                foreach ($retval as $x) {
                    if ($x instanceof PlayerAchievement) {
                        self::$notify[$a->id] = [$a, $x];
                    }
                }
            } else if ($retval instanceof PlayerAchievement) {
                self::$notify[$a->id] = [$a, $retval];
            }
        }
    }

    /**
     *
     * @param  SourceAchievement $a
     * @return PlayerAchievement
     */
    public function building(SourceAchievement $a, $argv, $intval, $strval)
    {
        $pa = new PlayerAchievement; switch ($a->type) {
        case SourceAchievement::TYPE_BRANCH: return $this->commonBranchAchievement($pa, $a);
        case SourceAchievement::TYPE_CYCLE:
            switch ($strval) {
            case 'collect':
                $retval = array(); if ($argv instanceof Object) {
                    foreach ($argv as $eid => $n) {
                        if ($eid == $a->argv) {
                            $retval[] = $this->commonCycleAchievement($pa, $a, $n);
                        }
                    }
                }

                return $retval;

            default: return $this->commonCycleAchievement($pa, $a);
            }

            break;
        }
    }

    /**
     *
     * @param  SourceAchievement $a
     * @param  mixed             $argv
     * @param  integer           $intval
     * @param  string            $strval
     * @return PlayerAchievement
     */
    public function crusade(SourceAchievement $a, $argv, $intval, $strval)
    {
        $pa = new PlayerAchievement; switch ($a->type) {
        case SourceAchievement::TYPE_BRANCH: return $this->commonBranchAchievement($pa, $a);
        case SourceAchievement::TYPE_CYCLE:  return $this->commonCycleAchievement($pa, $a);
        }
    }

    /**
     *
     * @param  SourceAchievement $a
     * @param  mixed             $argv
     * @param  integer           $intval
     * @param  string            $strval
     * @return PlayerAchievement
     */
    public function rob(SourceAchievement $a, $argv, $intval, $strval)
    {
        $pa = new PlayerAchievement; switch ($a->type) {
        case SourceAchievement::TYPE_BRANCH: return $this->commonBranchAchievement($pa, $a);
        case SourceAchievement::TYPE_CYCLE:  return $this->commonCycleAchievement($pa, $a);
        }
    }

    /**
     *
     * @param  SourceAchievement $a
     * @param  mixed             $argv
     * @param  integer           $intval
     * @param  string            $strval
     * @return PlayerAchievement
     */
    public function entity(SourceAchievement $a, $argv, $intval, $strval)
    {
        if ($a->argv && $a->argv <> $argv) {
            return;
        }

        $pa = new PlayerAchievement;
        $ck = $a->getCounterKey();

        if ($ck <> $this->k) {
            /* prevent multiple increment */
            $this->p->counter->$ck += $intval;
            $this->p->counter->save();

            $this->k = $ck;
        }

        if ($pa->find($this->p->id, $a->id) && $pa->flags <> PlayerAchievement::FLAG_WAIT) {
            return;
        }

        $pa->pid = $this->p->id;
        $pa->aid = $a->id;

        if ($this->p->counter->$ck >= $a->intval) {
            $pa->flags = PlayerAchievement::FLAG_INIT;
        } else {
            $pa->flags = PlayerAchievement::FLAG_WAIT;
        }

        $pa->save();
        if ($pa->flags == PlayerAchievement::FLAG_INIT) {
            return $pa;
        }
    }

    /**
     *
     * @param  SourceAchievement $a
     * @param  mixed             $argv
     * @param  integer           $intval
     * @param  string            $strval
     * @return PlayerAchievement
     */
    public function lineup(SourceAchievement $a, $argv, $intval, $strval)
    {
        $pa = new PlayerAchievement; switch ($a->type) {
        case SourceAchievement::TYPE_CYCLE: return $this->commonCycleAchievement($pa, $a);
        }
    }

    /**
     *
     * @param  SourceAchievement $a
     * @param  mixed             $argv
     * @param  integer           $intval
     * @param  string            $strval
     * @return PlayerAchievement
     */
    public function memcard(SourceAchievement $a, $argv, $intval, $strval)
    {
        /* expired */
        if ($this->p->profile->monthlyCardExpire < $this->global->ustime) {
            return;
        }

        /* already done */
        $ck = $a->getCounterKey(); if ($this->p->counterCycle->$ck) {
            return;
        }

        $this->p->counterCycle->$ck = 1;
        $this->p->counterCycle->save();

        $pa = new PlayerAchievement; if (false === $pa->find($this->p->id, $a->id)) {
            $pa->pid = $this->p->id;
            $pa->aid = $a->id;
        }

        $pa->flags = PlayerAchievement::FLAG_INIT;
        $pa->save();
        return $pa;
    }

    /**
     *
     * @param  SourceAchievement $a
     * @param  mixed             $argv
     * @param  integer           $intval
     * @param  string            $strval
     * @return PlayerAchievement
     */
    public function pvp(SourceAchievement $a, $argv, $intval, $strval)
    {
        $pa = new PlayerAchievement; switch ($a->type) {
        case SourceAchievement::TYPE_BRANCH: return $this->commonBranchAchievement($pa, $a);
        }
    }

    /**
     *
     * @param  SourceAchievement $a
     * @param  mixed             $argv
     * @param  integer           $intval
     * @param  string            $strval
     * @return PlayerAchievement
     */
    public function recruit(SourceAchievement $a, $argv, $intval, $strval)
    {
        $pa = new PlayerAchievement; switch ($a->type) {
        case SourceAchievement::TYPE_CYCLE: return $this->commonCycleAchievement($pa, $a);
        }
    }

    /**
     *
     * @param  SourceAchievement $a
     * @param  mixed             $argv
     * @param  integer           $intval
     * @param  string            $strval
     * @return PlayerAchievement
     */
    public function signin(SourceAchievement $a, $argv, $intval, $strval)
    {
        $pa = new PlayerAchievement; switch ($a->type) {
        case SourceAchievement::TYPE_CYCLE:
            return $this->commonCycleAchievement($pa, $a);
        }
    }

    /**
     *
     * @param  SourceAchievement $a
     * @param  mixed             $argv
     * @param  integer           $intval
     * @param  string            $strval
     * @return PlayerAchievement
     */
    public function task(SourceAchievement $a, $argv, $intval, $strval)
    {
        if ($a->argv && $a->argv <> $argv) {
            return;
        }

        $pa = new PlayerAchievement; switch ($a->type) {
        case SourceAchievement::TYPE_MAIN:
            return $this->commonMainAchievement($pa, $a);

        case SourceAchievement::TYPE_BRANCH:
            return $this->commonBranchAchievement($pa, $a);

        case SourceAchievement::TYPE_CYCLE:
            return $this->commonCycleAchievement($pa, $a);
        }
    }

    /**
     *
     * @return PlayerAchievement
     */
    private function commonMainAchievement(PlayerAchievement $pa, SourceAchievement $a)
    {
        $pa = $this->commonBranchAchievement($pa, $a);

        /* unlocking */
        if ($a->unlock) {
            $this->p->profile->{$a->unlock} = 'unlock';
            $this->p->profile->save();
        }

        return $pa;
    }

    /**
     *
     * @param  PlayerAchievement $pa
     * @param  SourceAchievement $a
     * @return PlayerAchievement
     */
    private function commonBranchAchievement(PlayerAchievement $pa, SourceAchievement $a)
    {
        if ($pa->find($this->p->id, $a->id)) {
            return;
        }

        $pa->pid   = $this->p->id;
        $pa->aid   = $a->id;
        $pa->flags = PlayerAchievement::FLAG_INIT;
        $pa->save();
        return $pa;
    }

    /**
     *
     * @param  PlayerAchievement $pa
     * @param  SourceAchievement $a
     * @param  integer           $incr
     * @return PlayerAchievement
     */
    private function commonCycleAchievement(PlayerAchievement $pa, SourceAchievement $a, $incr = 1)
    {
        $ck = $a->getCounterKey(); $first = false; if (empty($this->p->counterCycle->$ck)) {
            $first = true;
        }

        $this->p->counterCycle->$ck += $incr;
        $this->p->counterCycle->save();

        if ($pa->find($this->p->id, $a->id) && $pa->flags == PlayerAchievement::FLAG_DONE && false === $first) {
            /* already done in this cycle */
            return;
        }

        $pa->pid = $this->p->id;
        $pa->aid = $a->id;

        if ($this->p->counterCycle->$ck >= $a->intval) {
            $pa->flags = PlayerAchievement::FLAG_INIT;
        } else {
            $pa->flags = PlayerAchievement::FLAG_WAIT;
        }

        $pa->save();
        if ($pa->flags == PlayerAchievement::FLAG_INIT) {
            return $pa;
        }
    }
}

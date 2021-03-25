<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Player;

/**
 *
 */
use System\Stdlib\PhpPdo,
    System\Stdlib\Object,
    System\Object\DatabaseObject;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Config\NPCLineup,
    Zhanhd\Config\Task           as SourceTask,
    Zhanhd\Object\Player         as Owner,
    Zhanhd\Object\Player\Entity  as PlayerEntity,
    Zhanhd\Extension\Combat\Combatant;

/**
 *
 */
class Lineup extends DatabaseObject
{
    /**
     * @var integer
     */
    public $veryInitPeid = null;

    /**
     * @var Object
     */
    public $heros = null;

    /**
     * @var Formation
     */
    public $f = null;

    /**
     * @var integer
     */
    const CAPTAIN_POSITION = 4;

    /**
     * @var array
     */
    public static $groups = [
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
    ];

    /**
     * @var array
     */
    public static $positions = [
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
    ];

    /**
     * @return integer
     */
    public function getPower()
    {
        $powerSum = 0;
        foreach ($this->heros as $o) {
            if ($o->peid == 0) {
                continue;
            }

            $powerSum += $o->getPower($this);
        }

        return $powerSum;
    }

    /**
     * @return integer
     */
    public function getLvlsum()
    {
        $ret = 0;
        foreach ($this->heros as $o) {
            if ($o->peid == 0) {
                continue;
            }

            $ret += $o->pe->lvl;
        }

        return $ret;
    }

	/**
	 * @return array
	 */
	public function getBonds()
	{
		$bonds = [];
		$eids = [];
		$ret = [];
		foreach ($this->heros as $o) {
			if ($o->peid == 0) continue;
			if (empty($o->pe->e->bonds)) continue;
			$eids[$o->pe->eid] = $o->pos;
			foreach ($o->pe->e->bonds as $bond) {
				$bonds[$bond] = true;
			}
		}

		foreach ($bonds as $bid => $ig) {
			if (null === ($bond = Store::get('bond', $bid))) continue;
			if (empty($bond->members)) continue;
			$bonded = true;
			foreach ($bond->members as $eid) {
				if (false === isset($eids[$eid])) {
					$bonded = false;
					break;
				}
			}
			if ($bonded) {
				foreach ($bond->members as $eid) {
					$ret[$eids[$eid]][] = $bond->id;
				}
			}
		}
		return $ret;
	}

    /**
     * @param integer $energy
     * @return boolean
     */
    public function checkEnergy($energy)
    {
        $ustime = $this->retrieveScope('globals')->ustime;
        foreach ($this->heros as $plh) {
            if ($plh->peid == 0) continue;
            if ($plh->pe->property->getEnergy($ustime) < $energy) {
                return false;
            }
        }
        return true;
    }

    /**
     *
     * @return mixed
     */
    public function getCaptain()
    {
        foreach ($this->heros as $o) {
            if ($o->pos == static::CAPTAIN_POSITION && $o->peid) {
                return $o;
            }
        }

        return false;
    }

    /**
     *
     * @param  PhpPdo      $pdo
     * @param  Owner       $o
     * @param  string|null $index
     * @return Object
     */
    public static function gets(PhpPdo $pdo, Owner $o, $index = null)
    {
        return self::buildSet($pdo, __CLASS__, 'SELECT * FROM `zhanhd.player`.`PlayerLineup` WHERE `pid` = ?', array(
            $o->id
        ), true, $index);
    }

    /**
     *
     * @param  SourceTask $task
     * @param  integer    $difficulty
     * @return Lineup
     */
    public static function fromTask(SourceTask $task, $difficulty)
    {
        $o = new static;
        $o->gid = 1;
        $o->fid = $task->difficulties[$difficulty]->fid;

        foreach ($task->getCombatants($difficulty) as $tc) {
            if (null === ($e = Store::get('entity', $tc->eid))) {
                continue;
            }
            $pe = PlayerEntity::fromSourceEntity($e, $tc->el, $tc->sl - 1);

            $pe->id  = $task->id * 10 + $tc->pos;
            $pe->pid = $task->id;
            
            $plh = new Lineup\Hero;
            $plh->pe   = $pe;
            $plh->gid  = 1;
            $plh->pos  = $tc->pos;
            $plh->peid = $pe->id;

            $o->heros->set(null, $plh);
        }

        if ($o->fid) {
            $o->f = Store::get('formation', $o->fid);
        }

        return $o;
    }

    /**
     * @param NPCLineup $npc
     * @return Lineup
     */
    public static function fromNPCLineup(NPCLineup $npc)
    {
        $o = new static;
        $o->gid = 1;
        $o->fid = $npc->fid;

        if ($o->fid) {
            $o->f = Store::get('formation', $o->fid);
        }

        foreach ($npc->combatants as $c) {
            if (null === ($e = Store::get('entity', $c->eid))) {
                continue;
            }
            $pe = PlayerEntity::fromSourceEntity($e, $c->lvl, $c->ehc);

            $pe->id  = $npc->id * 10 + $c->pos;
            $pe->pid = $npc->id;

            $plh = new Lineup\Hero;
            $plh->pe   = $pe;
            $plh->gid  = 1;
            $plh->pos  = $c->pos;
            $plh->peid = $pe->id;

            $o->heros->set(null, $plh);
        }

        return $o;
    }

    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerLineup`';
    }

    /**
     *
     * @return array
     */
    public function columns()
    {
        return [
            'pid' => 0,
            'gid' => 0,
            'fid' => 0,
            'power'   => 0,
            'lvlsum'  => 0,
            'captain' => 0,
        ];
    }

    /**
     *
     * @return array
     */
    public function primary()
    {
        return [
            'pid' => null,
            'gid' => null,
        ];
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->heros = new Object;
    }

    /**
     *
     * @return void
     */
    protected function postSelect()
    {
        $this->heros = Lineup\Hero::gets($this->phppdo, $this);

        if ($this->fid) {
            $this->f = Store::get('formation', $this->fid);
        }
    }

    /**
     *
     * @return void
     */
    protected function postInsert()
    {
        foreach (self::$positions as $pos) {
            $plh = new Lineup\Hero;
            $plh->pid = $this->pid;
            $plh->gid = $this->gid;
            $plh->pos = $pos;

            if ($this->veryInitPeid && $pos == self::CAPTAIN_POSITION) {
                $plh->peid = $this->veryInitPeid;
            }

            $plh->save();
            $this->heros->set($pos, $plh);
        }
    }

    /**
     *
     * @return void
     */
    protected function preUpdate()
    {
        foreach ($this->heros as $plh) {
            if ($plh->pos == self::CAPTAIN_POSITION) {
                $this->captain = $plh->pe->eid;
            }
            $plh->save();
        }
    }

    /**
     *
     * @return void
     */
    protected function preDelete()
    {
        foreach ($this->heros as $plh) {
            $plh->drop();
        }
    }
}

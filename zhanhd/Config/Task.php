<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Config;

/**
 *
 */
use System\Object\ConfigObject;

/**
 *
 */
class Task extends ConfigObject
{
    /**
     * @struct self
     *
     * @id         integer
     * @prev       integer
     * @next       integer
     * @flags      integer
     * @fid        integer
     * @exp        integer
     * @combatants array( (integer)difficulty => array( (integer)pos => TaskCombatant ) )
     * @bonus      array( (integer)difficulty => array( (integer)idx => TaskBonus ) )
     */

    /**
     * @struct TaskCombatant
     *
     * @pos integer
     * @eid integer
     * @el  integer
     * @sl  integer
     */

    /**
     * @struct TaskBonus
     *
     * @prob  integer
     * @picks array( (integer)eid => (integer)num )
     */

    /**
     * @const integer
     */
    const VERY_INIT  = 1010100;

    /**
     * @const integer
     */
    const UNLOCK_TASK = 1050504;

    /**
     * @const type
     */
    const FLAG_FIGHT    = 1 << 0;
    const FLAG_RESOURCE = 1 << 1;
    const FLAG_BRANCH   = 1 << 2;
    const FLAG_RANDOM   = 1 << 3;
    const FLAG_UNUSED_A = 1 << 4;
    const FLAG_UNUSED_B = 1 << 5;
    const FLAG_UNUSED_C = 1 << 6;
    
    const FLAG_CHAIN_HEAD = 1 << 7;
    const FLAG_CHAIN_TAIL = 1 << 8;
    const FLAG_FINAL_TASK = 1 << 9;

    /**
     * @const integer
     */
    const DIFFICULTY_AVERAGE = 1;
    const DIFFICULTY_ELITE   = 2;
    const DIFFICULTY_HELL    = 3;

    /**
     * @return integer
     */
    public function getType()
    {
        return $this->flags & (static::FLAG_FIGHT | static::FLAG_RESOURCE | static::FLAG_BRANCH | static::FLAG_RANDOM);
    }
    
    /**
     * @return boolean
     */
    public function ishead()
    {
        return (boolean) (($this->flags & static::FLAG_CHAIN_HEAD) == static::FLAG_CHAIN_HEAD);
    }

    /**
     * @return boolean
     */
    public function istail()
    {
        return (boolean) (($this->flags & static::FLAG_CHAIN_TAIL) == static::FLAG_CHAIN_TAIL);
    }

    /**
     * @return boolean
     */
    public function isfinal()
    {
        return (boolean) (($this->flags & static::FLAG_FINAL_TASK) == static::FLAG_FINAL_TASK);
    }

    /**
     * @return integer
     */
    public function getRandom()
    {
        if ($this->getType() <> static::FLAG_RANDOM) {
            return;
        }
        
        $seed = array_sum($this->branches);
        $rand = mt_rand(1, $seed);

        foreach ($this->branches as $tid => $prob) {
            if ($rand <= $prob) {
                return $tid % 100;
            }

            $rand -= $prob;
        }
    }

    /**
     * @return integer
     */
    public function getDynastyId()
    {
        return (integer) ($this->id / 1000000);
    }

    /**
     * @return integer
     */
    public function getFightId()
    {
        return $this->id - $this->id % 100;
    }

    /**
     * @return array
     */
    public function getNextIds()
    {
        switch ($this->getType()) {
        case static::FLAG_FIGHT:
        case static::FLAG_RESOURCE:
            return array($this->next);
        
        case static::FLAG_BRANCH:
        case static::FLAG_RANDOM:
            return array_keys($this->branches);
        }
    }

    /**
     * @param integer $difficulty
     * @param boolean $isFirst
     * @return array
     */
    public function drop($difficulty, $isFirst)
    {
        if (false === isset($this->bonus) || false === isset($this->bonus[$difficulty])) {
            return null;
        }

        $items = [];
        $bonus = $this->bonus[$difficulty];
        if (count($bonus) == 0) {
            return null;
        }
        foreach ($bonus as $tb) {
            if ((boolean)$tb->first != $isFirst) {
                continue;
            }

            if (mt_rand(1, 10000) > $tb->prob) {
                continue;
            }

            if (false === isset($tb->picks)) {
                continue;
            }

            foreach ($tb->picks as $eid => $num) {
                if (Store::has('entity', $eid)) {
                    if (false === isset($items[$eid])) {
                        $items[$eid] = $num;
                    } else {
                        $items[$eid]+= $num;
                    }
                } else if (Store::has('egroup', $eid)) {
                    $picks = Store::get('egroup', $eid)->pick($num);
                    foreach ($picks as $eid => $num) {
                        if (false === isset($items[$eid])) {
                            $items[$eid] = $num;
                        } else {
                            $items[$eid]+= $num;
                        }
                    }
                }
            }
        }
        return $items;
    }

    /**
     * @param integer $difficulty
     * @return array
     */
    public function getCombatants($difficulty)
    {
        if (false === isset($this->combatants) || false === isset($this->combatants[$difficulty])) {
            return null;
        }

        return $this->combatants[$difficulty];
    }
}

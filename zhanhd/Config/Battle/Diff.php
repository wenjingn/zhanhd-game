<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Config\Battle;

/**
 *
 */
use System\Object\ConfigObject,
    System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Config\Store;

/**
 *
 */
class Diff extends ConfigObject
{
    /**
     * @struct self
     *
     * @diff  integer
     * @exp   integer
     * @power integer
     * @drops array(integer => BattleReward)
     */

    /**
     * @struct BattleReward
     *
     * @prob integer
     * @eid  integer
     * @num  integer
     */

    /**
     * @param boolean $isMember
     * @return integer
     */
    public function getExp($isMember = false)
    {
        if ($isMember) {
            return $this->exp + ($this->exp >> 1);
        }
        return $this->exp;
    }

    /**
     * @return array
     */
    public function drop()
    {
        $ret = [];
        foreach ($this->drops as $o) {
            if (mt_rand(1, 10000) > $o->prob) {
                continue;
            }
            
            if (false === Store::has('entity', $o->eid)) {
                continue;
            }
            
            if (isset($ret[$o->eid])) {
                $ret[$o->eid] += $o->num;
            } else {
                $ret[$o->eid] = $o->num;
            }
        }

        return $ret;
    }
}

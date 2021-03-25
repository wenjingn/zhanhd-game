<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Player\Entity;

/**
 *
 */
use System\Stdlib\PhpPdo,
    System\Stdlib\Object,
    System\Object\DatabaseProfile;

/**
 *
 */
class Skill extends DatabaseProfile
{
    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerEntitySkill`';
    }

    /**
     * @param integer $peid
     * @return void
     */
    public function setPlayerEntityId($peid)
    {
        $this->where->peid = $peid;
        return $this;
    }

    /**
     * @return integer
     */
    public function getPlayerEntityId()
    {
        return $this->where->peid;
    }

    public function skillPoint()
    {
        $sum = 0;
        foreach ($this as $v) {
            $sum += $v-1;
        }
        return $sum;
    }
}

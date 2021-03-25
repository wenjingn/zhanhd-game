<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Player\Lineup;

/**
 *
 */
use System\Object\DatabaseProfile;

/**
 *
 */
class Equip extends DatabaseProfile
{
    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerLineupEquip`';
    }

    /**
     *
     * @param  integer $pid
     * @return Equip
     */
    public function setPlayerId($pid)
    {
        $this->where->pid = $pid;
        return $this;
    }

    /**
     *
     * @param  integer $gid
     * @return Equip
     */
    public function setGroupId($gid)
    {
        $this->where->gid = $gid;
        return $this;
    }

    /**
     *
     * @param  integer $pos
     * @return Equip
     */
    public function setPosition($pos)
    {
        $this->where->pos = $pos;
        return $this;
    }
}

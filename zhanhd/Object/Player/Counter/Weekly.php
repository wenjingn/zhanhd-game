<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Player\Counter;

/**
 *
 */
use System\Object\DatabaseProfile;

/**
 *
 */
class Weekly extends DatabaseProfile
{
    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerCounterWeekly`';
    }

    /**
     *
     * @param  integer $pid
     * @return Profile
     */
    public function setPlayerId($pid)
    {
        $this->where->pid = $pid;
        return $this;
    }

    /**
     *
     * @return Profile
     */
    public function setWeek($week)
    {
        $this->where->week = $week;
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getWeek()
    {
        return $this->where->week;
    }
}

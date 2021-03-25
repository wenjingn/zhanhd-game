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
class Monthly extends DatabaseProfile
{
    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerCounterMonthly`';
    }

    /**
     * @param integer $pid
     * @return Profile
     */
    public function setPlayerId($pid)
    {
        $this->where->set('pid', $pid);
        return $this;
    }

    /**
     * @param integer $month
     * @return Profile
     */
    public function setMonth($month)
    {
        $this->where->set('month', $month);
        return $this;
    }

    /**
     * @return integer
     */
    public function getMonth()
    {
        return $this->where->month;
    }
}

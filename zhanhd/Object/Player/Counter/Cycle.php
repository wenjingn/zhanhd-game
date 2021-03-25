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
class Cycle extends DatabaseProfile
{
    /**
     * @const integer
     */
    const DAILY_ACTIVITY_LIMIT   = 5;
    const DAILY_MARRY_LIMIT      = 3;
    const DAILY_PVP_LIMIT        = 10;
    const DAILY_LOVE_LIMIT       = 5;
    const DAILY_TALENT_LIMIT     = 5;
    const DAILY_FRIENDSHIP_LIMIT = 40;
    const DAILY_QA_LIMIT         = 10;
    const DAILY_DAYINS_LIMIT     = 5;
    const DAILY_ROB_LIMIT        = 20;

    /**
     * @key map 不完全统计
     *
     * @signin 登陆
     * @sign   签到
     * @
     */

    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerCounterCycle`';
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
     * @param integer $cycle
     * @return Profile
     */
    public function setCycle($cycle)
    {
        $this->where->cycle = $cycle;
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getCycle()
    {
        return $this->where->cycle;
    }

    /**
     * @return integer
     */
    public function getDailyLoveTimes()
    {
        return $this->freeLove + $this->paidLove;
    }

    /**
     * @return integer
     */
    public function getDayInsTimes()
    {
        return self::DAILY_DAYINS_LIMIT + $this->dayinsBuy - $this->dayins;
    }
}

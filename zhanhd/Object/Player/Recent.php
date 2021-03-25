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
use System\Object\DatabaseProfile;

/**
 *
 */
class Recent extends DatabaseProfile
{
    /**
     * @const integer
     */
    const CD_FREE_LOVE  = 10000000;
    const CD_GUILD_JOIN = 86400*1000000;

    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerRecent`';
    }

    /**
     * @param integer $pid
     * @return Recent
     */
    public function setPlayerId($pid)
    {
        $this->where->pid = $pid;
        return $this;
    }

    /**
     * @param integer $now
     * @return integer
     */
    public function getFreeLoveCD($now)
    {
        return (integer) (max(0, $this->freeLove + self::CD_FREE_LOVE - $now) / 1000000);
    }

    /**
     * @param integer $now
     * @return integer
     */
    public function getCdJoinGuild($now)
    {
        return (int) (max(0, $this->leaveGuild + self::CD_GUILD_JOIN - $now)/1000000);
    }
}

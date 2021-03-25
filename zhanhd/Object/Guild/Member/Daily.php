<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Guild\Member;

/**
 *
 */
use System\Object\DatabaseProfile;

/**
 *
 */
class Daily extends DatabaseProfile
{
    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`GuildMemberDaily`';
    }

    /**
     * @param integer $gid
     * @return void
     */
    public function setGuildId($gid)
    {
        $this->where->gid = $gid;
        return $this;
    }

    /**
     * @param integer $pid
     * @return Daily
     */
    public function setPlayerId($pid)
    {
        $this->where->pid = $pid;
        return $this;
    }

    /**
     * @param integer $date
     * @return Daily
     */
    public function setDate($date)
    {
        $this->where->date = $date;
        return $this;
    }
}

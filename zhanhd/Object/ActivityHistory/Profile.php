<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\ActivityHistory;

/**
 *
 */
use System\Object\DatabaseProfile;

/**
 *
 */
class Profile extends DatabaseProfile
{
    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`ActivityHistoryProfile`';
    }

    /**
     *
     * @param integer $apid
     * @return Profile
     */
    public function setActivityPlanId($apid)
    {
        $this->where->aid = $apid;
        return $this;
    }

    /**
     * @param integer $pid
     * @return Profile
     */
    public function setPlayerId($pid)
    {
        $this->where->pid = $pid;
        return $this;
    }
}

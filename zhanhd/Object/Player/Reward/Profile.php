<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Player\Reward;

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
        return '`zhanhd.player`.`PlayerRewardProfile`';
    }

    /**
     *
     * @param  integer $prid
     */
    public function setPlayerRewardId($prid)
    {
        $this->where->prid = $prid;
    }
}

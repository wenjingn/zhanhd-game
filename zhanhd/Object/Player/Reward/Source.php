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
class Source extends DatabaseProfile
{
    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerRewardSource`';
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

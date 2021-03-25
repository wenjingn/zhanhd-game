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
class Counter extends DatabaseProfile
{
    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerCounter`';
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
}

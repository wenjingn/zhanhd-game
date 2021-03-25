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
use System\Object\DatabaseProfile,
    System\Stdlib\PhpPdo;

/**
 *
 * @property $currtask    max-task-id       of un-done task
 * @property $lasttask    max-task-chain-id of done task
 * @property @lastetask   max-task-chain-id of done elite task
 * @property @unlocketask unlock            of elite task
 */
class Profile extends DatabaseProfile
{
    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerProfile`';
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

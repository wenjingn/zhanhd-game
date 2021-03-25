<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Config;

/**
 *
 */
use System\Object\ConfigObject;

/**
 *
 */
class InviteReward extends ConfigObject
{
    /**
     * @struct self
     *
     * @id     integer
     * @limit  integer
     * @source array((integer)eid => (integer)num)
     */

    /**
     * @return string
     */
    public function getCounterKey()
    {
        return sprintf('invite-reward-%d', $this->id);
    }
}

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
class FixedTimeReward extends ConfigObject
{
    /**
     * @struct self
     *
     * @id integer
     * @sec integer
     * @source array((integer)eid => (integer)num)
     */

    /**
     * @return string
     */
    public function getCounterKey()
    {
        return sprintf('online-time-reward-%d', $this->id);
    }
}

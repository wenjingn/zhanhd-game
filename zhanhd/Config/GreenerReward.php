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
class GreenerReward extends ConfigObject
{
    /**
     * @struct self
     *
     * @day integer 
     * @eid integer
     * @num integer
     */

    /**
     * @return array
     */
    public function getRewards()
    {
        return [$this->eid => $this->num];
    }
}

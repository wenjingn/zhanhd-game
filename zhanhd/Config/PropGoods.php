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
class PropGoods extends ConfigObject
{
    /**
     * @struct self
     *
     * @id     integer
     * @price  integer
     * @incr   integer
     * @eid    integer
     */

    /**
     * @return array
     */
    public function getRewards()
    {
        return [$this->eid => 1];
    }

    /**
     * @return string
     */
    public function getCounterKey()
    {
        return sprintf('goods-%d', $this->id);
    }
}

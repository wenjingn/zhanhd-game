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
class FriendShipGoods extends ConfigObject
{
    /**
     * @struct self
     *
     * @id     integer
     * @eid    integer
     * @price  integer
     * @weight integer
     */

    /**
     * @return array
     */
    public function getRewards()
    {
        return [$this->eid => 1];
    }
}

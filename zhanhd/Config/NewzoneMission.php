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
class NewzoneMission extends ConfigObject
{
    /**
     * @struct self
     *
     * @day     integer
     * @idx     integer
     * @type    integer
     * @intval  integer
     * @extra   integer
     * @rewards array((integer)eid => (integer)num)
     */

    /**
     * @const integer
     */
    const TYPE_DEPOSIT = 1;
    const TYPE_LOGIN   = 2;
    const TYPE_LVLSUM  = 3;
    const TYPE_TASK    = 4;
    const TYPE_RECRUITEQUIP = 5;
    const TYPE_RECRUITHERO  = 6;
    const TYPE_PVPRANK      = 7;
    const TYPE_HEROGAIN     = 8;

    /**
     * @return integer
     */
    public function getDay()
    {
        return (integer)($this->id/100);
    }

    /**
     * @return integer
     */
    public function getIdx()
    {
        return $this->id % 100;
    }

    /**
     * @return string
     */
    public function getCounterKey()
    {
        return sprintf('newzoneMission-%d', $this->id);
    }
}

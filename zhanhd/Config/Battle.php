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
use System\Object\ConfigObject,
    System\Stdlib\Object;

/**
 *
 */
class Battle extends ConfigObject
{
    /**
     * @struct self
     *
     * @id    integer
     * @diff  array((integer)diff => (BattleDiff))
     *
     */

    /**
     * @return BattleDiff
     */
    public function getDiff($diff)
    {
        if (false === isset($this->diff[$diff])) {
            return null;
        }
        return $this->diff[$diff];
    }
}

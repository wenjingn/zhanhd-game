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
class Gift extends ConfigObject
{
    /**
     * @struct self
     *
     * @id      integer
     * @type    integer
     * @release integer
     * @expire  integer
     * @source  [(integer) eid => (integer) num]
     */

    /**
     * @return array
     */
    public function getRewards()
    {
        return $this->source;
    }
}

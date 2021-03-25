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
class Goods extends ConfigObject
{
    /**
     * @struct self
     *
     * @id          integer
     * @tag         string
     * @epid        integer
     * @gold        integer
     * @incr        integer
     * @enable      integer
     * @requirement array( (integer)eid => (integer)num )
     */

    /**
     *
     * @return string
     */
    public function getCounterKey()
    {
        return sprintf('goods-%s', $this->id);
    }
}

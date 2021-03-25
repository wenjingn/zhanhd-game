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
use Zhanhd\Object\Player\Lineup;

/**
 *
 */
class DayIns extends ConfigObject
{
    /**
     * @struct self
     *
     * @id     integer
     * @unlock integer
     * @diff   [(integer)diff => DayInsDiff]
     */

    /**
     * @param integer $diff
     * @return array
     */
    public function getDiff($diff)
    {
        if (false === isset($this->diff) || false === isset($this->diff[$diff])) {
            return false;
        }
        return $this->diff[$diff];
    }

    /**
     * @return string
     */
    public function getCounterKey()
    {
        return sprintf('dayins-%d', $this->id);
    }
}

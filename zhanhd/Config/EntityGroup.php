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
class EntityGroup extends ConfigObject
{
    /**
     * @struct self
     * 
     * @items array( (integer)eid => (integer)prob )
     */
    
    /**
     * @param  integer $times
     * @return integer|null
     */
    public function pick($times = 1)
    {
        if (false === isset($this->items) || ($count = count($this->items)) === 0) {
            return [];
        }
        
        $picked = [];
        $seed = $count-1;
        for ($i = 0; $i < $times; $i++) {
            $rand= mt_rand(0, $seed);

            $eid = $this->items[$rand];
            if (false === isset($picked[$eid])) {
                $picked[$eid] = 1;
            } else {
                $picked[$eid] ++;
            }
        }

        return $picked;
    }
    
    /**
     * @return integer
     */
    public function pickone()
    {
        if (false === isset($this->items) || ($count = count($this->items)) === 0) {
            return null;
        }

        $rand = mt_rand(0, $count-1);
        return $this->items[$rand];
    }
}

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
class Building extends ConfigObject
{
    /**
     * @struct self
     *
     * @id          integer
     * @tag         string
     * @enable      boolean
     * @collectable boolean
     * @level       array( (integer) level => (object) BuildingProperty )
     */


    /**
     * @struct BuildingProperty
     * @ugdur        integer
     * @ctmin        integer
     * @ctmax        integer
     * @cost         integer
     * @productions  array( (integer) eid => (integer) num )
     * @upgradations array( (integer) eid => (integer) num )
     */
}

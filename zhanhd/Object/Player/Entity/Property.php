<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Player\Entity;

/**
 *
 */
use System\Object\DatabaseProfile;

/**
 *
 */
class Property extends DatabaseProfile
{
    /**
     * @return integer
     */
    public function getEnergy()
    {
        $ustime = $this->retrieveScope('globals')->ustime;
        if ($this->energy < 100) {
            return min(
                $this->energy + (integer) (($ustime - $this->energyusedat) / 60000000),
                100
            );
        }

        return $this->energy;
    }

    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerEntityProperty`';
    }

    /**
     *
     * @param  integer $peid
     * @return Property
     */
    public function setPlayerEntityId($peid)
    {
        $this->where->peid = $peid;
        return $this;
    }
}

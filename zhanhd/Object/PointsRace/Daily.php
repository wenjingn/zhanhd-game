<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\PointsRace;

/**
 *
 */
use System\Object\DatabaseObject;

/**
 *
 */
class Daily extends DatabaseObject
{
    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PointsRaceDaily`';
    }

    
    /**
     * @return array
     */
    public function primary()
    {
        return [
            'cycle' => null,
            'cday'  => null,
            'pid'   => null,
        ];
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'cycle' => 0,
            'cday'  => 0,
            'pid'   => 0,
            'challenged'=> 0,
            'refreshed' => 0,
            'conswin'   => 0,
        ];
    }
}

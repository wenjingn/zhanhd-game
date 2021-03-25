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
use System\Object\DatabaseObject,
    System\Stdlib\PhpPdo;

/**
 *
 */
class Target extends DatabaseObject
{
    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PointsRaceTarget`';
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'cycle' => null,
            'pid'   => null,
            'tid'   => null,
        ];
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'cycle' => 0,
            'pid'   => 0,
            'tid'   => 0,
            'status'=> 0,
        ];
    }

    /**
     * @param PhpPdo $pdo
     * @param integer $cycle
     * @param integer $pid
     * @return Object
     */
    public static function gets(PhpPdo $pdo, $cycle, $pid)
    {
        return self::buildSet($pdo, __CLASS__, 'SELECT * FROM `zhanhd.player`.`PointsRaceTarget` WHERE `cycle`=? AND `pid`=?', [
            $cycle, $pid,
        ]);
    }
}

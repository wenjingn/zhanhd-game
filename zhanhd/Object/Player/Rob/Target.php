<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Player\Rob;

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
        return '`zhanhd.player`.`RobTarget`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'pid'    => 0,
            'tid'    => 0,
            'status' => 0,
        ];
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'pid' => 0,
            'tid' => 0,
        ];
    }

    /**
     * @param PhpPdo $pdo
     * @return Object
     */
    public static function gets(PhpPdo $pdo, Tarlist $l)
    {
        return self::buildSet($pdo, __CLASS__, 'SELECT * FROM `zhanhd.player`.`RobTarget` WHERE `pid`=?', [
            $l->pid,
        ]);
    }
}

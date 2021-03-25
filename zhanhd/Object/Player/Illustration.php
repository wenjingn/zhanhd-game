<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Player;

/**
 *
 */
use System\Stdlib\PhpPdo,
    System\Object\DatabaseObject;

/**
 *
 */
use Zhanhd\Object\Player as Owner;

/**
 *
 */
class Illustration extends DatabaseObject
{
    /**
     *
     * @param  PhpPdo $pdo
     * @param  Owner  $o
     * @return Object
     */
    public static function gets(PhpPdo $pdo, Owner $o)
    {
        return self::buildSet($pdo, __CLASS__, 'SELECT * FROM `zhanhd.player`.`PlayerIllustration` WHERE `pid` = ?', array(
            $o->id
        ));
    }

    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerIllustration`';
    }

    /**
     *
     * @return array
     */
    public function columns()
    {
        return [
            'pid'  => 0,
            'eid'  => 0,
            'type' => 0,
        ];
    }

    /**
     *
     * @return array
     */
    public function primary()
    {
        return [
            'pid' => null,
            'eid' => null,
        ];
    }
}

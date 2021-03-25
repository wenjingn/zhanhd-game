<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Guild\Impeach;

/**
 *
 */
use System\Object\DatabaseObject;

/**
 *
 */
class Member extends DatabaseObject
{
    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`GuildImpeachMember`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'gid' => 0,
            'pid' => 0,
        ];
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'gid' => null,
        ];
    }

    /**
     * @param PhpPdo  $pdo
     * @param integer $gid
     * @return Object
     */
    public static function gets($pdo, $gid)
    {
        return self::buildSet($pdo, __CLASS__, 'SELECT * FROM `zhanhd.player`.`GuildImpeachMember` WHERE `gid`=?', [$gid], false, 'pid');
    }
}

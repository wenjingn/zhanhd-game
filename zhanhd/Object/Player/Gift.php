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
use System\Object\DatabaseObject;

/**
 *
 */
class Gift extends DatabaseObject
{
    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerGift`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'pid' => 0,
            'gid' => 0,
        ];
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'pid' => null,
            'gid' => null,
        ];
    }
}

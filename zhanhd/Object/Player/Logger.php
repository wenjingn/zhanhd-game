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
class Logger extends DatabaseObject
{
    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerLogger`';
    }

    /**
     *
     * @return array
     */
    public function columns()
    {
        return [
            'id'      => null,

            'pid'     => 0,
            'cmd'     => 0,

            'eid'     => 0,
            'cnt'     => 0,

            'peid'    => 0,
            'created' => 0,
        ];
    }

    /**
     *
     * @return array
     */
    public function primary()
    {
        return [
            'id' => null,
        ];
    }
}

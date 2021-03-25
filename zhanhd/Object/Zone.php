<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object;

/**
 *
 */
use System\Object\DatabaseObject;

/**
 *
 */
class Zone extends DatabaseObject
{
    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`Zone`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'k' => '',
            'v' => '',
        ];
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'k' => null,    
        ];
    }
}

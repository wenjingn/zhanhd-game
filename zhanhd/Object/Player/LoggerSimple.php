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
class LoggerSimple extends DatabaseObject
{
    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerLoggerSimple`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'id'  => 0,
            'pid' => 0,
            'log' => '',
            'created' => 0,
        ];
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'id' => null,    
        ];
    }

    /**
     * @return void
     */
    protected function preInsert()
    {
        $this->created = $this->ustime;
    }
}

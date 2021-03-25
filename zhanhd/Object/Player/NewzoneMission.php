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
class NewzoneMission extends DatabaseObject
{
    /**
     * @const integer
     */
    const FLAG_INIT = 0;
    const FLAG_DONE = 1;
    const FLAG_ACCEPT = 2;

    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerNewzoneMission`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'pid'     => 0,
            'mid'     => 0,
            'flag'    => 0,
            'created' => 0,
            'updated' => 0,
        ];
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'pid' => null,
            'mid' => null,
        ];
    }

    /**
     * @return void
     */
    protected function preInsert()
    {
        $this->created = $this->ustime;
    }

    /**
     * @return void
     */
    protected function preUpdate()
    {
        $this->updated = $this->ustime;
    }
}

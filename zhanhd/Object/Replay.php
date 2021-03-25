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
class Replay extends DatabaseObject
{
    /**
     * @const integer
     */
    const ACCESS_ATTACKER = 1;
    const ACCESS_DEFENDER = 2;
    const ACCESS_OTHERS   = 4;
    const ACCESS_ALL      = 255;

    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`Replay`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'id'       => 0,
            'attacker' => 0,
            'defender' => 0,
            'combat'   => '',
            'access'   => 0,
            'release'  => 0,
            'created'  => 0,
        ];
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'id' => false,
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
    protected function postInsert()
    {
        $this->id = $this->phppdo->lastInsertId();
    }
}

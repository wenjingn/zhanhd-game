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
use Zhanhd\Config\Store;

/**
 *
 */
class Task extends DatabaseObject
{
    /**
     * @var integer
     */
    const FLAG_INIT = 1;
    const FLAG_DONE = 2;

    /**
     *
     * @param  integer $ustime
     * @return void
     */
    public function execute($ustime)
    {
        $this->flags   = self::FLAG_DONE;
        $this->updated = $ustime;
        $this->save();
    }

    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerTask`';
    }

    /**
     *
     * @return array
     */
    public function columns()
    {
        return [
            'pid'        => 0,
            'fid'        => 0,
            'difficulty' => 0,
            'flags'      => 0,
            'created'    => 0,
            'updated'    => 0,
        ];
    }

    /**
     *
     * @return array
     */
    public function primary()
    {
        return [
            'pid'        => null,
            'fid'        => null,
            'difficulty' => null,
        ];
    }

    /**
     *
     * @return void
     */
    protected function postSelect()
    {
    }

    /**
     *
     * @return void
     */
    protected function preInsert()
    {
        $this->created = $this->ustime;
    }

    /**
     *
     * @return void
     */
    protected function preUpdate()
    {
        $this->updated = $this->ustime;
    }
}

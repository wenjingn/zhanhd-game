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
class ActivityHistory extends DatabaseObject
{
    /**
     * @var ActivityHistory\Profile
     */
    public $profile = null;

    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`ActivityHistory`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'aid'   => 0,
            'pid'   => 0,
            'rank'  => 0,
            'score' => 0,
        ];
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'aid' => null,
            'pid' => null,
        ];
    }

    /**
     * @return void
     */
    protected function initial()
    {
        $this->profile = new ActivityHistory\Profile;
    }

    /**
     * @return void
     */
    protected function postSelect()
    {
        $this->profile->setActivityPlanId($this->aid);
        $this->profile->setPlayerId($this->pid);
        $this->profile->find();
    }

    /**
     * @return void
     */
    protected function postInsert()
    {
        $this->profile->setActivityPlanId($this->aid);
        $this->profile->setPlayerId($this->pid);
        $this->profile->save();
    }

    /**
     * @return void
     */
    protected function postUpdate()
    {
        $this->profile->save();
    }

    /**
     * @return void
     */
    protected function preDelete()
    {
        $this->profile->drop();
    }
}

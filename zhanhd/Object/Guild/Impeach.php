<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Guild;

/**
 *
 */
use System\Stdlib\Object,
    System\Object\DatabaseObject;

/**
 *
 */
class Impeach extends DatabaseObject
{
    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`GuildImpeach`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'gid'  => 0,
            'time' => 0,
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
     * @var Object
     */
    public $members = null;
    
    /**
     * @return void
     */
    protected function postSelect()
    {
        $this->members = Impeach\Member::gets($this->phppdo, $this->gid);
    }

    /**
     * @return void
     */
    protected function preDelete()
    {
        foreach ($this->members as $o) {
            $o->drop();
        }
    }

    /**
     * @param GuildMember $guildMember
     * @return void
     */
    public function addMember(Member $guildMember)
    {
        $impeachMember = new Impeach\Member;
        $impeachMember->gid = $this->gid;
        $impeachMember->pid = $guildMember->pid;
        $impeachMember->save();
        if ($this->members === null) {
            $this->members = new Object;
        }
        $this->members->set($impeachMember->pid, $impeachMember);
    }
}

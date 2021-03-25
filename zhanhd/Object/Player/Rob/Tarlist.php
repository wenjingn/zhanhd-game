<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Player\Rob;

/**
 *
 */
use System\Object\DatabaseObject,
    System\Stdlib\Object;

/**
 *
 */
class Tarlist extends DatabaseObject
{
    /**
     * @const integer
     */
    const MAXCOUNT  = 9;
    const CDREFRESH = 1800000000;

    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`RobTarlist`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'pid'   => 0,
            'robbed' => 0,
            'total' => 0,
            'refresh' => 0,
        ];
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'pid' => 0,
        ];
    }

    /**
     * @return void
     */
    public function dropTargets()
    {
        $stmt = $this->phppdo->prepare('DELETE FROM `zhanhd.player`.`RobTarget` WHERE `pid`=?');
        $stmt->execute([$this->pid]);
    }

    /**
     * @return Object
     */
    public function getTargets()
    {
        return Target::gets($this->phppdo, $this);
    }

    /**
     * @param array $ranlist
     * @return Object
     */
    public function genTargets($ranlist)
    {
        $this->robbed = 0;
        $this->total = count($ranlist);
        $this->save();
        $targets = new Object;
        foreach ($ranlist as $tid) {
            $target = new Target;
            $target->pid = $this->pid;
            $target->tid = $tid;
            $target->save();
            $targets->set(null, $target);
        }
        return $targets;
    }
}

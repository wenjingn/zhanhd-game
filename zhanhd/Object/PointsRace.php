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
use System\Stdlib\Object,
    System\Object\DatabaseObject;

/**
 *
 */
class PointsRace extends DatabaseObject
{
    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PointsRace`';
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'cycle' => null,
            'pid'   => null,
        ];
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'cycle' => 0,
            'pid' => 0,
            'buff' => 0,
            'cwin' => 0,
            'challenged' => 0,

            'listWin'   => 0,
            'listTotal' => 0,
        ];
    }

    /**
     * @return array
     */
    public function getTargets()
    {
        return PointsRace\Target::gets($this->phppdo, $this->cycle, $this->pid);
    }

    /**
     * @return void
     */
    public function dropTargets()
    {
        $stmt = $this->phppdo->prepare('DELETE FROM `zhanhd.player`.`PointsRaceTarget` WHERE `cycle`=? AND `pid`=?');
        $stmt->execute([$this->cycle, $this->pid,]);
    }

    /**
     * @param array $list
     * @return Object
     */
    public function genTargets($list)
    {
        $this->listWin = 0;
        $this->listTotal = count($list);
        $this->save();

        $targets = new Object;
        foreach ($list as $tid) {
            $target = new PointsRace\Target;
            $target->cycle = $this->cycle;
            $target->pid = $this->pid;
            $target->tid = $tid;
            $target->save();
            $targets->set(null, $target);
        }
        return $targets;
    }
}

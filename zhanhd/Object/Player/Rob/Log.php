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
    System\Stdlib\PhpPdo;

/**
 *
 */
class Log extends DatabaseObject
{
    /**
     * @const integer
     */
    const RETVAL_DEFEND  = 1;
    const RETVAL_REVENGE = 2;

    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`RobLog`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'id'     => 0,
            'pid'    => 0,
            'robber' => 0,
            'retval' => 0,
            'replay' => 0,
            'weapon' => 0,
            'armor'  => 0,
            'soldier'=> 0,
            'gold'   => 0,
            'horse'  => 0,
            'created'=> 0,
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
     * @param array $resources
     * @return void
     */
    public function setResources($resources)
    {
        foreach ($resources as $eid => $num) {
            switch ($eid) {
            case 2:
                $this->weapon = $num;
                break;
            case 3:
                $this->armor = $num;
                break;
            case 4:
                $this->soldier = $num;
                break;
            case 6:
                $this->gold = $num;
                break;
            case 7:
                $this->horse = $num;
                break;
            default:
                break;
            }
        }
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

    /**
     * @return integer
     */
    public function getResourceTotal()
    {
        return $this->weapon+$this->armor+$this->soldier+$this->gold+$this->horse;
    }

    /**
     * @param PhpPdo $pdo
     * @param integer $pid
     * @param integer $ustime
     * @return Object
     */
    public static function getsByPid(PhpPdo $pdo, $pid, $ustime)
    {
        return self::buildSet($pdo, __CLASS__, 'select * from `zhanhd.player`.`RobLog` where `pid`=? and `created`+86400000000>?', [
            $pid, $ustime,
        ]);
    }
}

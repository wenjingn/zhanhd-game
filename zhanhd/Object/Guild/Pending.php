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
use System\Stdlib\PhpPdo,
    System\Object\DatabaseObject;

/**
 *
 */
class Pending extends DatabaseObject
{
    /**
     * @return void
     */
    public $player = null;

    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`GuildPending`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'gid' => 0,
            'pid' => 0,
            'time'=> 0,
        ];
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'gid' => null,
            'pid' => null,
        ];
    }

    /**
     * @return void
     */
    protected function preInsert()
    {
        $this->time = $this->ustime;
    }

    /**
     * @param PhpPdo  $pdo
     * @param integer $pid
     * @return Object
     */
    public static function getsByPid(PhpPdo $pdo, $pid)
    {
        return self::buildSet($pdo, __CLASS__, 'SELECT * FROM `zhanhd.player`.`GuildPending` WHERE `pid`=?', [$pid]);
    }

    /**
     * @param PhpPdo $pdo
     * @param integer $gid
     * @return Object
     */
    public static function getsByGid(PhpPdo $pdo, $gid)
    {
        return self::buildSet($pdo, __CLASS__, 'SELECT * FROM `zhanhd.player`.`GuildPending` WHERE `gid`=?', [$gid]);
    }

    /**
     * @return integer
     */
    public function getPost()
    {
        return Member::POST_GREENER;
    }

    /**
     * @param Player $p
     */
    public function convertMember($p)
    {
        $this->player = $p;
    }
}

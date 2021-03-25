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
use System\Stdlib\PhpPdo,
    System\Object\DatabaseObject;

/**
 *
 */
use Zhanhd\Object\Player as Owner;

/**
 *
 */
class Achievement extends DatabaseObject
{
    /**
     * @var const
     */
    const FLAG_INIT = 1;
    const FLAG_DONE = 2;
    const FLAG_WAIT = 3;

    /**
     *
     * @param  PhpPdo  $pdo
     * @param  Owner   $o
     * @param  string  $index
     * @return Object
     */
    public static function gets(PhpPdo $pdo, Owner $o, $index = null)
    {
        return self::buildSet($pdo, __CLASS__, 'SELECT * FROM `zhanhd.player`.`PlayerAchievement` WHERE `pid` = ?', array(
            $o->id,
        ), false, $index);
    }

    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerAchievement`';
    }

    /**
     *
     * @return array
     */
    public function columns()
    {
        return [
            'pid'     => 0,
            'aid'     => 0,
            'flags'   => 0,
            'created' => 0,
            'updated' => 0,
        ];
    }

    /**
     *
     * @return array
     */
    public function primary()
    {
        return [
            'pid' => null,
            'aid' => null,
        ];
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

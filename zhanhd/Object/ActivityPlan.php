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
use System\Object\DatabaseObject,
    System\Stdlib\PhpPdo,
    System\Stdlib\CacheableStatementTrait;

/**
 *
 */
class ActivityPlan extends DatabaseObject
{
    /**
     *
     */
    use CacheableStatementTrait;

    /**
     * @var integer
     */
    protected $dur;

    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`ActivityPlan`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'id'     => 0,
            'preview'=> 0,
            'begin'  => 0,
            'end'    => 0,
            'type'   => 0,
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
     * @return void
     */
    protected function postSelect()
    {
        $dur = ($this->end - $this->begin) * 1000;
        $this->dur = (integer) pow(10, strlen($dur));
    }

    /**
     * @param PhpPdo $pdo
     * @return Object
     */
    public static function getsAll(PhpPdo $pdo)
    {
        return self::buildSet($pdo, __CLASS__, 'select * from `zhanhd.player`.`ActivityPlan`', [], true);
    }

    /**
     * @param PhpPdo  $pdo
     * @param integer $time
     * @return Object
     */
    public static function getsFresh(PhpPdo $pdo, $time)
    {
        return self::buildSet($pdo, __CLASS__, 'SELECT * FROM `zhanhd.player`.`ActivityPlan` where `preview` < ? and `end` > ?', [
            $time,
            $time,
        ], true);
    }

    /**
     * @param integer $time
     * @param integer $type
     * @return Object
     */
    public function findByType($time, $type)
    {
        return $this->findBySql('SELECT * FROM `zhanhd.player`.`ActivityPlan` WHERE `begin`<? AND `end`>? AND `type`=? LIMIT 1', [
            $time, $time, $type,
        ]);
    }

    /**
     * @param integer $ustime
     * @return Object
     */
    public static function ends(PhpPdo $pdo, $time)
    {
        $time = (integer) ($ustime / 1000000);
        $sql  = sprintf('SELECT * FROM `zhanhd.player`.`ActivityPlan` where `end` < ? and `status` <> %d', self::STATUS_SETTLED);
        //$sql = 'SELECT * FROM `zhanhd.player`.`ActivityPlan` where id = 1';
        return self::buildSet($pdo, __CLASS__, $sql, [
            $time,
        ], true);
    }

    /**
     * @return string
     */
    public function redisKey()
    {
        return 'zhanhd:st:activity:' . $this->id;
    }

    /**
     * @param integer $encoded
     * @return array
     */
    public function decode($encoded)
    {
        if (empty($encoded)) {
            return [
                'score'  => 0,
                'ustime' => 0,
            ];
        }

        return [
            'score'  => (integer)($encoded / $this->dur),
            'ustime' => (integer)($this->end * 1000000 - ($encoded % $this->dur) * 1000),
        ];
    }

    /**
     * @param integer $score
     * @param integer $ustime
     * @return string
     */
    public function encode($score, $ustime)
    {
        return $score * $this->dur + (integer)(($this->end * 1000000 - $ustime) / 1000);
    }
}

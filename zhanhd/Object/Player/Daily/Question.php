<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Player\Daily;

/**
 *
 */
use System\Object\DatabaseObject;

/**
 *
 */
class Question extends DatabaseObject
{
    /**
     * @const integer
     */
    const MAX_LIMIT = 10;

    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerDailyQuestion`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'pid'    => 0,
            'day'    => 0,
            'qid'    => 0,
            'answer' => 0,
            'idx'    => 0,
        ];
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'pid' => null,
            'day' => null,
            'qid' => null,
        ];
    }

    /**
     * @param PhpPdo  $pdo
     * @param integer $pid
     * @param integer $day
     * @return Object
     */
    public static function gets($pdo, $pid, $day)
    {
        $sql = 'SELECT * FROM `zhanhd.player`.`PlayerDailyQuestion` where `pid` = ? and `day` = ? order by `idx`';
        return self::buildSet($pdo, __CLASS__, $sql, [
            $pid,
            $day,
        ], false, 'qid');
    }
}

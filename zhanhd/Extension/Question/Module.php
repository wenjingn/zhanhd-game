<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Question;

/**
 *
 */
use Exception;

/**
 *
 */
use Zhanhd\Config\Store;

/**
 *
 */
class Module
{
    /**
     * @const integer
     */
    const QUESTION_COUNT = 10; 

    /**
     * @return array
     */
    public static function generate()
    {
        $all = array_keys(Store::get('question'));
        shuffle($all);

        return array_slice($all, 0, self::QUESTION_COUNT);
    }

    /**
     * @param redis $redis
     * @param integer $date
     * @param array $list
     * @return void
     */
    public static function push($redis, $date, $list)
    {
        if (empty($list)) {
            throw new Exception('empty questions');
        }

        $key = sprintf('zhanhd:lt:question:%d', $date);
        $redis->rpush($key, ...$list);
        $redis->expire($key, 2*86400);
    }

    /**
     * @param redis $redis
     * @param integer $date
     * @return array
     */
    public static function fetch($redis, $date)
    {
        $key = sprintf('zhanhd:lt:question:%d', $date);
        return $redis->lrange($key, 0, self::QUESTION_COUNT - 1);
    }
}

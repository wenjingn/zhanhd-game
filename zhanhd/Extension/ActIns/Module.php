<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\ActIns;

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
    const INS_COUNT = 3;
    const RANK_LENGTH = 20;
    const SCORE_OFFSET = 10000000000;

    /**
     * @param Object  $g
     * @return array
     */
    public static function generate($g)
    {
        $redis = $g->redis;
        $all = array_keys(Store::get('actins'));
        $key = sprintf('zhanhd:lt:actins:%d', $g->week-1);
        $last = $redis->lindex($key, -1);
        
        $index = array_search($last, $all);
        if ($index === false) {
            $index = -1;
        }
        $len   = count($all);
        $ret   = [];
        for ($i = 0; $i < self::INS_COUNT; $i++) {
            $ret[] = $all[(++$index)%$len];
        }
        return $ret;
    }

    /**
     * @param redis $redis
     * @param integer $week
     * @param array $list
     * @return void
     */
    public static function push($redis, $week, $list)
    {
        if (empty($list)) {
            throw new Exception('empty actins');
        }

        $key = sprintf('zhanhd:lt:actins:%d', $week);
        $redis->rpush($key, ...$list);
        $redis->expire($key, 8*86400);
    }

    /**
     * @param redis $redis
     * @param integer $week
     * @return array
     */
    public static function fetch($redis, $week)
    {
        $key = sprintf('zhanhd:lt:actins:%d', $week);
        return $redis->lrange($key, 0, self::INS_COUNT - 1);
    }

    /**
     * @param Redis $redis
     * @param integer $week
     * @param integer $pid
     * @param integer $ts
     * @return boolean
     */
    public static function incrScore($redis, $week, $pid, $ts)
    {
        $key = sprintf('zhanhd:st:actins:%d', $week);
        $ts  = 86400*7*1000 - ((int)($ts/1000) - strtotime('monday')*1000);
        $score = (integer)$redis->zScore($key, $pid);
        $score = (integer)($score/self::SCORE_OFFSET) + 1;
        $scoreWithTs = $score*self::SCORE_OFFSET + $ts;
        return $redis->zAdd($key, $scoreWithTs, $pid);
    }

    /**
     * @param Redis $redis
     * @param integer $week
     * @param integer $pid
     * @return integer
     */
    public static function getScore($redis, $week, $pid)
    {
        $key = sprintf('zhanhd:st:actins:%d', $week);
        $score = (integer)$redis->zScore($key, $pid);
        return (int)($score/self::SCORE_OFFSET);
    }

    /**
     * @param Redis $redis
     * @param integer $week
     * @param integer $pid
     * @return integer
     */
    public static function rank($redis, $week, $pid)
    {
        $key = sprintf('zhanhd:st:actins:%d', $week);
        $rank = $redis->zrevrank($key, $pid);
        if (false === $rank) {
            $rank = -1;
        }

        return $rank+1;
    }

    /**
     * @param Redis $redis
     * @param integer $week
     * @return array
     */
    public static function rankList($redis, $week)
    {
        $key = sprintf('zhanhd:st:actins:%d', $week);
        $rank = $redis->zrevrange($key, 0, self::RANK_LENGTH, true);
        $ret = [];
        foreach ($rank as $k => $v) {
            $ret[$k] = (integer)($v/self::SCORE_OFFSET);
        }
        return $ret;
    }
}

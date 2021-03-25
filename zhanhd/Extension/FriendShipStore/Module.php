<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\FriendShipStore;

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
    const GOODS_COUNT = 9;

    /**
     * @return array
     */
    public static function generate()
    {
        $allGoods = Store::get('friendShipGoods');
        $all = [];
        foreach ($allGoods as $o) {
            $all[$o->id] = $o->prob;
        }
        
        $picks = [];
        for ($i = 0, $seed = array_sum($all); $i < self::GOODS_COUNT; $i++) {
            $rand = mt_rand(1, $seed);
            foreach ($all as $id => $prob) {
                if ($rand <= $prob) {
                    $picks[] = $id;
                    $seed -= $prob;
                    unset($all[$id]);
                    break;
                }
                $rand -= $prob;
            }
        }

        return $picks;
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
            throw new Exception('empty friendship goods');
        }

        $key = sprintf('zhanhd:lt:friendshipstore:%d', $date);
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
        $key = sprintf('zhanhd:lt:friendshipstore:%d', $date);
        return $redis->lrange($key, 0, self::GOODS_COUNT - 1);
    }
}

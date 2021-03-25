<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension;

/**
 *
 */
use Zhanhd\Config\Store;

/**
 *
 */
class PointsRace 
{
    /**
     * @var integer
     */
    public $cycle;
    public $ctime;
    public $cday;

    /**
     * @var Redis
     */
    private $redis = null;
    private $stkey = null;

    /**
     * @param Global $g
     * @return void
     */
    public function __construct($g)
    {
        $this->cycle = (int)($g->epoch/1209600);
        $this->ctime = $g->epoch%1209600;
        $this->cday  = (int)($this->ctime/86400);

        $this->redis = $g->redis;
        $this->stkey = sprintf('zhanhd:st:pointsrace:%d', $this->cycle);
        $this->stkey2= sprintf('zhanhd:st:pointsrace2:%d', $this->cycle);
    }

    /**
     * @param integer $pid
     * @return void
     */
    public function add($pid)
    {
        $this->redis->zadd($this->stkey, 0, $pid);
    }

    /**
     * @param integer $pid
     * @param integer $power
     * @return void
     */
    public function updatePower($pid, $power)
    {
        $this->redis->zadd($this->stkey2, $power, $pid);
    }

    /**
     * @param integer $pid
     * @param integer $score
     * @return void
     */
    public function incr($pid, $score)
    {
        $this->redis->zincrby($this->stkey, $score, $pid);
    }

    /**
     * @param integer $pid
     * @return integer
     */
    public function rank($pid)
    {
        return $this->redis->zrevrank($this->stkey, $pid)+1;
    }

    /**
     * @param integer $pid
     * @return integer
     */
    public function score($pid)
    {
        return $this->redis->zscore($this->stkey, $pid);
    }

    /**
     * @return array
     */
    public function ranks()
    {
        return $this->redis->zrevrange($this->stkey, 0, 9, true);
    }

    /**
     * @param integer $pid
     * @return array
     */
    public function genlist($pid)
    {
        $score = $this->score($pid);
        if ($score < 300) {
            $count = $this->redis->zcard('zhanhd:st:player-power');
            if ($score < 100) {
                $high = (int)($count*80/100);
                $low  = $count;
            } else {
                $high = (int)($count*40/100);
                $low  = (int)($count*65/100);
            }
            $list = $this->redis->zrevrange('zhanhd:st:player-power', $high-1, $low-1);
        } else {
            if ($score < 600) {
                $key = $this->stkey;
            } else {
                $key = $this->stkey2;
            }
            $rank = $this->redis->zrevrank($key, $pid);
            $high = max(0, $rank - 30);
            $low  = $rank+30;
            $list = $this->redis->zrevrange($key, $high, $low);
        }
        $i = array_search($pid, $list);
        if ($i !== false) {
            unset($list[$i]);
        }
        shuffle($list);
        return array_slice($list, 0, 5);
    }

    /**
     * @param integer $score
     * @return integer
     */
    public function getWinScore($score)
    {
        if ($score < 100) {
            return 5;
        } else if ($score < 300) {
            return 10;
        } else if ($score < 600) {
            return 15;
        } else {
            return 20;
        }
    }

    /**
     * @param integer $score
     * @param integer $conswin
     * @return integer
     */
    public function getConswinScore($score, $conswin)
    {
        if ($score < 100) {
            return $conswin;
        } else if ($score < 300) {
            return 2*$conswin;
        } else if ($score < 600) {
            return 3*$conswin;
        } else {
            return 4*$conswin;
        }
    }

    /**
     * @var static
     */
    protected static $rewards = [
        10 => 5,
        20 => 10,
        35 => 35,
        50 => 50,
        60 => 60,
        70 => 70,
        80 => 80,
        90 => 90,
        100 => 100,
    ];

    /**
     * @param integer $cwin
     * @return array
     */
    public function getCycleWinRewards($cwin)
    {
        if (false === isset(self::$rewards[$cwin])) {
            return [];
        }
        
        return [
            10  => self::$rewards[$cwin],
        ];
    }
}

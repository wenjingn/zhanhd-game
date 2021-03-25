<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\WorldBoss;

/**
 *
 */
use System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Extension\Combat\Combatant;

/**
 *
 */
class Module
{
    /**
     * @const integer
     */
    const STATUS_WAITING  = 1;
    const STATUS_ARRIVING = 2;
    const STATUS_KILLED   = 3;
    const STATUS_ESCAPE   = 4;

    /**
     * @return void
     */
    public function __construct($globals)
    {
        $this->redis = $globals->redis;
        $this->time  = $globals->time;
        $this->begin = strtotime($globals->date) + 20*3600;
        $this->end   = $this->begin + 1800;
        $this->htkey = sprintf('zhanhd:ht:worldboss:%s', $globals->date);
        $this->stkey = sprintf('zhanhd:st:worldboss:%s', $globals->date);
        $this->pykey = sprintf('zhanhd:ht:worldboss:players:%s', $globals->date);
        $this->bi    = null;
    }

    /**
     * @return Object
     */
    public function getStatus()
    {
        $status = new Object;
        if ($this->time < $this->begin) {
            $status->status  = self::STATUS_WAITING;
            $status->leftsec = $this->begin-$this->time;
            return $status;
        }
        $bi = $this->getBossInfo();
        if ($bi->chp <= 0) {
            $status->status = self::STATUS_KILLED;
            $status->leftsec= 0;
            return $status;
        }
        if ($this->time < $this->end) {
            $status->status = self::STATUS_ARRIVING;
            $status->leftsec= $this->end-$this->time;
            return $status;
        }
        $status->status = self::STATUS_ESCAPE;
        $status->leftsec= 0;
        return $status;
    }

    /**
     * @return integer
     */
    public function getLeftTime()
    {
        if ($this->time < $this->begin) {
            return $this->begin - $this->time;
        }
        if ($this->time < $this->end) {
            return $this->end - $this->time;
        }
        return 0;
    }

    /**
     * @return boolean
     */
    public function checkTime()
    {
        if ($this->time < $this->begin || $this->time > $this->end) {
            return false;
        }
        return true;
    }

    /**
     * @return void
     */
    public function generate()
    {
        $day = (int)(($this->time - Store::EPOCH)/86400);
        $bosses = Store::get('worldboss');
        if (empty($bosses)) {
            throw new Exception('empty worldboss');
        }

        $idx = $day%count($bosses);
        $i = 0;
        foreach ($bosses as $boss) {
            if ($i == $idx) {
                break;
            }
            $i++;
        }
        $ltkey = sprintf('zhanhd:ht:worldboss:%s', date('Ymd', $this->time-86400));
        $ltbi  = (new Object)->import($this->redis->hGetAll($ltkey));
        if (empty($ltbi->id)) {
            $lvl = 1;
        } else {
            if ($ltbi->chp <= 0) {
                $lvl = $ltbi->lvl+1;
            } else {
                $lvl = $ltbi->lvl;
            }
        }

        $l = $boss->getNpcLineup($lvl);
        $c = new Combatant($l->heros->get(0), 0);
        $hp = $c->getRawHpoint();

        $this->redis->hmset($this->htkey, [
            'id'  => $boss->id,
            'chp' => $hp,
            'rhp' => $hp,
            'lvl' => $lvl,
            'over'=> 0,
        ]);
        $this->redis->expire($this->htkey, 86400*2);
    }

    /**
     * @return Object
     */
    public function getBossInfo()
    {
        if ($this->bi === null) {
            $this->bi = (new Object)->import($this->redis->hGetAll($this->htkey));
        }
        return $this->bi;
    }

    /**
     * @param integer $pid
     * @return Object
     */
    public function getPlayerInfo($pid)
    {
        $rank = $this->redis->zRevRank($this->stkey, $pid);
        $rank = $rank === false ? 0 : $rank+1;
        return (new Object)->import([
            'rank'   => $rank,
            'damage' => $this->redis->zScore($this->stkey, $pid),
        ]);
    }

    /**
     * @param integer $pid
     * @param integer $damage
     * @return Object|false
     */
    public function attack($pid, $damage)
    {
        $bosshp = $this->redis->hIncrBy($this->htkey, 'chp', -$damage);
        if (-$bosshp >= $damage) {
            return false;
        }

        $dmgsum = $this->redis->zIncrBy($this->stkey, $damage, $pid);
        return (new Object)->import([
            'bosshp' => $bosshp,
            'damage' => $damage,
            'dmgsum' => $dmgsum,
        ]);
    }

    /**
     * @param integer $pid
     * @return void
     */
    public function enter($pid)
    {
        $this->redis->hset($this->pykey, $pid, 1);
    }

    /**
     * @param integer $pid
     * @return void
     */
    public function quit($pid)
    {
        $this->redis->hdel($this->pykey, $pid);
    }

    /**
     * @param integer $i
     * @param integer $j
     * @return array
     */
    public function getRankList($i, $j)
    {
        return $this->redis->zrevrange($this->stkey, $i, $j, true);
    }

    /**
     * @return void
     */
    public function over()
    {
        $this->redis->hset($this->htkey, 'over', 1);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Rank;

/**
 *
 */
use System\Stdlib\SharedResourceTrait;

/**
 *
 */
use Zhanhd\Object\Player,
    Zhanhd\Config\Store;

/**
 *
 */
class Module
{
    /**
     *
     */
    use SharedResourceTrait;

    /**
     * @var Redis
     */
    private $redis = null;

    /**
     * @var string
     */
    private $using = null;

    /**
     * @var string
     */
    const KEY_PLAYER_LEVEL = 'zhanhd:st:player-level';
    const KEY_PLAYER_POWER = 'zhanhd:st:player-power';
    const KEY_GUILD_RANK   = 'zhanhd:st:guild-rank';

    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->redis = $this->retrieveResource('redis', 'globals');
    }

    /**
     *
     * @param  string $key
     * @return void
     */
    public function using($key)
    {
        switch ($key) {
        case self::KEY_PLAYER_LEVEL:
        case self::KEY_PLAYER_POWER:
        case self::KEY_GUILD_RANK:
            $this->using = $key;
            break;
        }
    }

    /**
     *
     * @param  mixed $m
     * @param  mixed $s
     * @param integer $t
     * @return void
     */
    public function push($m, $s, $t)
    {
        $s = $s*10000000000+10000000000+Store::EPOCH-$t;
        $this->redis->zAdd($this->using, $s, $m);
    }

    /**
     * @param integer $score
     * @return integer
     */
    public function parse($s)
    {
        return (int)($s/10000000000);
    }

    /**
     * @param integer $g
     * @param integer $s
     * @return void
     */
    public function pushGuild($g, $s)
    {
        $this->redis->zAdd($this->using, $s, $g);
    }

    /**
     *
     * @param  mixed $m
     * @return integer
     */
    public function rank($m)
    {
        return $this->redis->zRevRank($this->using, $m);
    }

    /**
     *
     * @param  integer $i
     * @param  integer $j
     * @return array
     */
    public function range($i, $j)
    {
        return $this->redis->zRevRange($this->using, $i, $j, true);
    }

    /**
     *
     * @return void
     */
    public function clean()
    {
        $this->redis->del($this->using);
    }
}

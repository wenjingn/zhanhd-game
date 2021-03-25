<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\PvpRank;

/**
 *
 */
use System\Stdlib\SharedResourceTrait;

/**
 *
 */
use Zhanhd\Object\Player;

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
    private $prkey = 'zhanhd:st:pvp';

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
     * @param  Player $p
     * @return void
     */
    public function push(Player $p)
    {
        $retry = 5;
        while (true) {
            $rank = $this->redis->zSize($this->prkey) + 1;
            $this->redis->watch($this->prkey);

            if (false !== $this->redis->multi()->zAdd($this->prkey, $rank, $p->id)->exec()) {
                return;
            }

            if ($retry == 0) {
                break;
            }

            usleep(10000);
            $retry--;
        }
    }

    /**
     *
     * @param  Player $p
     * @return integer
     */
    public function rank(Player $p)
    {
        return $this->redis->zScore($this->prkey, $p->id);
    }

    /**
     *
     * @param  integer $i
     * @param  integer $j
     * @param  boolean $withScore
     * @return array
     */
    public function range($i, $j, $withScore = true)
    {
        return $this->redis->zRangeByScore($this->prkey, $i, $j, array(
            'withscores' => $withScore,
        ));
    }

    /**
     *
     * @param  Player $dst
     * @param  Player $src
     * @return boolean
     */
    public function exchange(Player $dst, Player $src)
    {
        $i = $this->redis->zScore($this->prkey, $dst->id);
        $j = $this->redis->zScore($this->prkey, $src->id);

        if ($i < $j) {
            return true;
        }

        $this->redis->watch($this->prkey);
        return $this->redis->multi()
                           ->zAdd($this->prkey, $i, $src->id, $j, $dst->id)
                           ->exec();
    }
}

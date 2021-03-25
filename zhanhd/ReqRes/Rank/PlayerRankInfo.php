<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Rank;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Str,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\Object\Player;

/**
 *
 */
class PlayerRankInfo extends Box
{
    /**
     *
     * @param  Player  $p
     * @param  integer $rank
     * @return void
     */
    public function fromPlayerObject(Player $p, $rank, $level, $power)
    {
        $this->rank    ->intval($rank);
        $this->level   ->intval($level);
        $this->power   ->intval($power);
        $this->nickname->strval($p->name);
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('rank',     new U16);
        $this->attach('level',    new U16);
        $this->attach('power',    new U32);
        $this->attach('nickname', new Str);
    }
}

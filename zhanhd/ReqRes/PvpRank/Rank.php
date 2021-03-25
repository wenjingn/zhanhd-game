<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\PvpRank;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Str,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\ReqRes\LeaderInfo,
    Zhanhd\Object\Player;

/**
 *
 */
class Rank extends Box
{
    /**
     *
     * @param  Player  $p
     * @param  integer $rank
     * @return void
     */
    public function fromPlayerObject(Player $p, $rank)
    {
        $l = $p->getLineup(1);

        $this->pid   ->intval($p->id);
        $this->power ->intval($l->power);
        $this->rank  ->intval($rank);
        $this->leader->fromPlayerObject($p);
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('pid',    new U64);
        $this->attach('power',  new U32);
        $this->attach('rank',   new U32);
        $this->attach('leader', new LeaderInfo);
    }
}

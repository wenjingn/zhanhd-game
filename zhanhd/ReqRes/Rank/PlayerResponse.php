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
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32,
    System\ReqRes\Set;

/**
 *
 */
use Zhanhd\ReqRes\Lineup\Hero\Request as Lineup;

/**
 *
 */
class PlayerResponse extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(231);

        $this->attach('myLevel', new U16);
        $this->attach('myPower', new U32);

        $this->attach('myPowerRank', new U16);
        $this->attach('myLevelRank', new U16);

        $this->attach('powerRanks',  new Set(new PlayerRankInfo));
        $this->attach('levelRanks',  new Set(new PlayerRankInfo));
        $this->attach('lineups',     new Set(new Lineup));
    }
}

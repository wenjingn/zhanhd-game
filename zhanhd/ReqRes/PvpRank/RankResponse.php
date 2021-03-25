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
class RankResponse extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(224);

        $this->attach('myRank',  new U16);
        $this->attach('myPower', new U32);
        $this->attach('ranks',   new Set(new Target));
        $this->attach('lineups', new Set(new Lineup));
    }
}

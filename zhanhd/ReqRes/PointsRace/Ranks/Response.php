<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\PointsRace\Ranks;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set;

/**
 *
 */
use Zhanhd\ReqRes\Rank\PlayerRankInfo;

/**
 *
 */
class Response extends ReqResHeader
{
    /**
     * return void
     */
    protected function setupResponse()
    {
        $this->command->intval(259);
        $this->attach('ranks', new Set(new PlayerRankInfo));
    }
}

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
    System\ReqRes\Set;

/**
 *
 */
class GuildResponse extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(233);

        $this->attach('level', new U16);
        $this->attach('rank',  new U16);

        $this->attach('ranks', new Set(new GuildRankInfo));
    }
}

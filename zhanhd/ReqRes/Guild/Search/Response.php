<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild\Search;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set;

/**
 *
 */
use Zhanhd\ReqRes\Guild\SimpleInfo;

/**
 *
 */
class Response extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(193);
        $this->attach('guilds', new Set(new SimpleInfo));
    }
}

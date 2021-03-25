<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Relation\Blacklist\Remove;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set,
    System\ReqRes\Int\U64;

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
        $this->command->intval(169);
        $this->attach('pids', new Set(new U64));
    }
}

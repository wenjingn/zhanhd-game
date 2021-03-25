<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Rob\Log;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set,
    System\ReqRes\Int\U08;

/**
 *
 */
use Zhanhd\ReqRes\Rob\Log;

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
        $this->command->intval(249);
        $this->attach('callHelp', new U08);
        $this->attach('logs', new Set(new Log));
    }
}

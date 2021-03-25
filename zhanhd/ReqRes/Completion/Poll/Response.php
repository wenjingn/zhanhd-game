<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Completion\Poll;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set,
    System\ReqRes\Int\U08;

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
        $this->command->intval(266);
        $this->attach('status', new Set(new U08));
    }
}

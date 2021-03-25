<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Chat;

/**
 *
 */
use System\Swoole\ReqResHeader,
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
        $this->command->intval(165);
        $this->attach('to', new U64);
        $this->attach('chat', new Info);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\MessageMail;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set,
    System\ReqRes\Int\U64;

/**
 *
 */
class NotifyResponse extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(55);
    }
}

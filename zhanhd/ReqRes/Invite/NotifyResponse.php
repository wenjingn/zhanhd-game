<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Invite;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

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
        $this->command->intval(133);
        $this->attach('invcount', new U32);
    }
}

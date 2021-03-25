<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Unlock;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U16;

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
        $this->command->intval(271);
        $this->attach('unlockId', new U16);
    }
}

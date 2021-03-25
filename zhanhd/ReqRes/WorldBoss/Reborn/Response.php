<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\WorldBoss\Reborn;

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
        $this->command->intval(185);
        $this->attach('buyTimes', new U16);
    }
}

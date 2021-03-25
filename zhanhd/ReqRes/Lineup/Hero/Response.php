<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Lineup\Hero;

/**
 *
 */
use System\Swoole\ReqResHeader;

/**
 *
 */
class Response extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(15);
        $this->attach('retval', new Request);
    }
}

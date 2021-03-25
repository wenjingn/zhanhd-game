<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Player\Create;

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
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(107);
    }
}

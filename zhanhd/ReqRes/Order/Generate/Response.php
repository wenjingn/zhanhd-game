<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Order\Generate;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Str,
    System\ReqRes\Int\U32;

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
        $this->command->intval(105);
        $this->attach('merchandise', new U32);
        $this->attach('order', new Str);
    }
}

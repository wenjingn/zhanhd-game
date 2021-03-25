<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Platform\Tencent\Pay;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

/**
 *
 */
class Response extends ReqResHeader
{
    /**
     * @return void
     */
    public function setupResponse()
    {
        $this->command->intval(119);
        $this->attach('balance', new U32);
    }
}

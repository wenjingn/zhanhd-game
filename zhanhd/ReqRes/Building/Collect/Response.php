<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Building\Collect;

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
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(8);

        $this->attach('bid', new U32);
        $this->attach('num', new U32);
        $this->attach('ttfull', new U32);
    }
}

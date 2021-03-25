<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Hero\Transfer;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U64,
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
        $this->command->intval(98);
        $this->attach('peid', new U64);
        $this->attach('aid',  new U32);
    }
}

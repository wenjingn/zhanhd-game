<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Forge;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

/**
 * @deprecated
 */
class Response extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(183);
        $this->attach('peid',  new U64);
        $this->attach('level', new U32);
    }
}

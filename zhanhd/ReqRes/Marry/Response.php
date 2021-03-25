<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Marry;

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
        $this->command->intval(84);
        $this->attach('wife', new U64);
        $this->attach('ring', new U32);
    }
}

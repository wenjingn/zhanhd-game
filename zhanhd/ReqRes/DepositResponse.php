<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

/**
 *
 */
class DepositResponse extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(85);
        $this->attach('merchandise', new U32);
        $this->attach('num',         new U32);
    }
}

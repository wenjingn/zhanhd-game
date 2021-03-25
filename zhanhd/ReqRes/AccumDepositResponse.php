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
class AccumDepositResponse extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(128);
        $this->attach('deposit', new U32);
    }
}

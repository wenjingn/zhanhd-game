<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Platform\Tencent;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

/**
 *
 */
class BalanceResponse extends ReqResHeader
{
    /**
     * @return void
     */
    public function setupResponse()
    {
        $this->command->intval(117);
        $this->attach('balance', new U32);
        $this->attach('rate', new U32);
        $this->rate->intval(1);
    }
}

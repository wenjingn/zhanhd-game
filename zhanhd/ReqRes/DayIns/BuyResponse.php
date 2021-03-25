<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\DayIns;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U16;

/**
 *
 */
class BuyResponse extends ReqResHeader
{
    /**
     * @return void
     */
    public function setupResponse()
    {
        $this->command->intval(173);
        $this->attach('times', new U16);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild;

/**
 *
 */
use System\Swoole\ReqResHeader;

/**
 *
 */
class ExpNotify extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(234);
        $this->attach('expinfo', new ExpInfo);
    }
}

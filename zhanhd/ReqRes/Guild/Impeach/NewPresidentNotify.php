<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild\Impeach;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\ReqRes\LeaderInfo;

/**
 *
 */
class NewPresidentNotify extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(220);
        $this->attach('pid',    new U64);
        $this->attach('leader', new LeaderInfo);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild\Manage\Transfer;

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
class Response extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(206);
        $this->attach('pid',    new U64);
        $this->attach('leader', new LeaderInfo);
    }
}

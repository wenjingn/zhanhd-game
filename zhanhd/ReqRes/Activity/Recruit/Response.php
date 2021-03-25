<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Activity\Recruit;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\Activity\RankInfo;

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
        $this->command->intval(90);
        $this->attach('aid', new U32);
    }
}

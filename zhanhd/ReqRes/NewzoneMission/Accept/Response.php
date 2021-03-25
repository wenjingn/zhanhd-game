<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\NewzoneMission\Accept;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U16;

/**
 *
 */
use Zhanhd\ReqRes\RewardInfo;

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
        $this->command->intval(158);
        $this->attach('day', new U16);
        $this->attach('idx', new U16);
        $this->attach('rewards', new RewardInfo);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\WeekMission\Accept;

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
        $this->command->intval(163);
        $this->attach('mid', new U16);
        $this->attach('rewards', new RewardInfo);
    }
}

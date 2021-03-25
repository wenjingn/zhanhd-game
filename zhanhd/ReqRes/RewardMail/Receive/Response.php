<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\RewardMail\Receive;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set,
    System\ReqRes\Int\U64;

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
        $this->command->intval(54);
        $this->attach('rewardIds', new Set(new U64));
        $this->attach('rewards', new RewardInfo);
    }
}

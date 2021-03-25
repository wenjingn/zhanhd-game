<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Invite\Reward;

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
        $this->command->intval(135);
        $this->attach('irid',   new U16);
        $this->attach('reward', new RewardInfo);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\RechargeReward;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

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
        $this->command->intval(226);
        $this->attach('id',  new U32);
        $this->attach('rewards', new RewardInfo);
    }
}

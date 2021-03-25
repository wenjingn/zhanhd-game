<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\DepositReward;

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
        $this->command->intval(130);
        $this->attach('drid', new U16);
        $this->attach('rewards', new RewardInfo);
    }
}

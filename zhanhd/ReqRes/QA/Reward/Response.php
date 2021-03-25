<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\QA\Reward;

/**
 *
 */
use System\Swoole\ReqResHeader;

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
        $this->command->intval(144);
        $this->attach('rewards', new RewardInfo);
    }
}

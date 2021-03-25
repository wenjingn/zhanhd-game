<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Completion\Accept;

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
        $this->command->intval(268);
        $this->attach('reward', new RewardInfo);
    }
}

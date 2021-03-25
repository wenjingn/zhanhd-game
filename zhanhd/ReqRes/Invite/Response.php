<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Invite;

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
        $this->command->intval(132);
        $this->attach('reward', new RewardInfo);
    }
}

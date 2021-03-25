<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Store\FriendShip;

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
        $this->command->intval(142);
        $this->attach('rewards', new RewardInfo);
    }
}

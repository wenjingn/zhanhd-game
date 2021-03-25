<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Store;

/**
 *
 */
use System\Swoole\ReqResHeader;

/**
 *
 */
class FriendShipStore extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(140);
        $this->attach('friendShipStore', new FriendShipInfo);
    }
}

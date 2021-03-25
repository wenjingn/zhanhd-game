<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Relation\Confirm;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set;

/**
 *
 */
use Zhanhd\ReqRes\FriendInfo;

/**
 *
 */
class Response extends ReqResHeader
{
    /**
     * @return void
     */
    public function setupResponse()
    {
        $this->command->intval(122);
        $this->attach('friends', new Set(new FriendInfo));
    }
}

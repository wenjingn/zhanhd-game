<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Relation\Blacklist;

/**
 *
 */
use System\Swoole\ReqResHeader;

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
    protected function setupResponse()
    {
        $this->command->intval(167);
        $this->attach('friend', new FriendInfo);
    }
}

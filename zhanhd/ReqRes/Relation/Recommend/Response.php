<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Relation\Recommend;

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
    protected function setupResponse()
    {
        $this->command->intval(63);
        $this->attach('strangers', new Set(new FriendInfo));
    }
}

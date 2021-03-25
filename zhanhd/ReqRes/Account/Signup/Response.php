<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Account\Signup;

/**
 *
 */
use System\Swoole\ReqResHeader;

/**
 *
 */
use Zhanhd\ReqRes\Account\UserInfo,
    Zhanhd\ReqRes\LeaderInfo;

/**
 * @deprecated
 */
class Response extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(3);
        $this->attach('user', new UserInfo);
    }
}

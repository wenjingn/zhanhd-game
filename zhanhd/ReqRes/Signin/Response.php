<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Signin;

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
        $this->command->intval(100);
        $this->attach('flag',    new U16);
        $this->attach('day',     new U16);
        $this->attach('rewards', new RewardInfo);
    }
}

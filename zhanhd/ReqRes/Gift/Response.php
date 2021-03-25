<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Gift;

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
        $this->command->intval(82);
        $this->attach('rewards', new RewardInfo);
    }
}

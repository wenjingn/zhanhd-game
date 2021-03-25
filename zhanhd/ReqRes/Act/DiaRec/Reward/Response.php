<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Act\DiaRec\Reward;

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
        $this->command->intval(238);
        $this->attach('rid', new U16);
        $this->attach('rewards', new RewardInfo);
    }
}

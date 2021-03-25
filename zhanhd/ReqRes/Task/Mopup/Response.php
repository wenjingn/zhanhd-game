<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Task\Mopup;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

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
        $this->command->intval(139);
        $this->attach('num', new U32);
        $this->attach('exp', new U32);
        $this->attach('reward', new RewardInfo);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild\Chest;

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
        $this->command->intval(218);
        $this->attach('score',   new U32);
        $this->attach('rewards', new RewardInfo);
    }
}

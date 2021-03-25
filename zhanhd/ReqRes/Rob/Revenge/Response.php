<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Rob\Revenge;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U08;

/**
 *
 */
use Zhanhd\ReqRes\Rob\Log,
    Zhanhd\ReqRes\CombatProcessInfo,
    Zhanhd\ReqRes\RewardInfo;

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
        $this->command->intval(251);
        $this->attach('log', new Log);
        $this->attach('helpTimes', new U08);
        $this->attach('combat', new CombatProcessInfo);
        $this->attach('reward', new RewardInfo);
    }
}

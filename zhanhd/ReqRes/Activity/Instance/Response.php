<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Activity\Instance;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\CombatProcessInfo,
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
        $this->command->intval(82);
        $this->attach('combat', new CombatProcessInfo);
        $this->attach('reward', new RewardInfo);
        $this->attach('exp',    new U32);
    }
}

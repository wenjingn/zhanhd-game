<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Rob\Attack;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set,
    System\ReqRes\Int\U08;

/**
 *
 */
use Zhanhd\ReqRes\CombatProcessInfo,
    Zhanhd\ReqRes\Rob\Target,
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
        $this->command->intval(246);
        $this->attach('robSuccessTimes', new U08);
        $this->attach('reward', new RewardInfo);
        $this->attach('combat', new CombatProcessInfo);
        $this->attach('targets', new Set(new Target));
    }
}

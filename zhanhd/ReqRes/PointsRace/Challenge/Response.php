<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\PointsRace\Challenge;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U16;

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
        $this->command->intval(263);
        $this->attach('combat', new CombatProcessInfo);
        $this->attach('reward', new RewardInfo);
        $this->attach('score', new U16);
        $this->attach('rank', new U16);
    }
}

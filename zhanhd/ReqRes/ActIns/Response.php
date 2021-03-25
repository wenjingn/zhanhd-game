<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\ActIns;

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
        $this->command->intval(146);
        $this->attach('aid', new U16);
        $this->attach('floor', new U16);
        $this->attach('combat', new CombatProcessInfo);
        $this->attach('reward', new RewardInfo);
    }
}

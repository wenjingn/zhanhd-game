<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\DayIns;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U16,
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
        $this->command->intval(171);
        $this->attach('iid',    new U16);
        $this->attach('diff',   new U16);
        $this->attach('times',  new U16);
        $this->attach('combat', new CombatProcessInfo);
        $this->attach('eid',    new U32);
        $this->attach('num',    new U32);
    }
}

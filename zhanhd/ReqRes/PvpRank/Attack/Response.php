<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\PvpRank\Attack;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\CombatProcessInfo;

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
        $this->command->intval(44);

        $this->attach('combat', new CombatProcessInfo);
        $this->attach('rank',   new U32);
        $this->attach('num',    new U32);
    }
}

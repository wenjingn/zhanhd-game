<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\WorldBoss\Attack;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

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
        $this->command->intval(179);
        $this->attach('cycle',  new U32);
        $this->attach('dmgsum', new U64);
        $this->attach('combat', new CombatProcessInfo);
    }
}

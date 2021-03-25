<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Replay;

/**
 *
 */
use System\Swoole\ReqResHeader;

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
        $this->command->intval(253);
        $this->attach('combat', new CombatProcessInfo);
    }
}

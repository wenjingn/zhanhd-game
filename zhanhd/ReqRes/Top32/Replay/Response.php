<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Top32\Replay;

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
        $this->command->intval(242);
        $this->attach('combat', new CombatProcessInfo);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Player;

/**
 *
 */
use System\Swoole\ReqResHeader;

/**
 *
 */
use Zhanhd\ReqRes\LeaderInfo;

/**
 *
 */
class LeaderResponse extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(48);
        $this->attach('leader', new LeaderInfo);
    }
}

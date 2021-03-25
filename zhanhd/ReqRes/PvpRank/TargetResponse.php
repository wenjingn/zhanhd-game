<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\PvpRank;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32,
    System\ReqRes\Set;

/**
 *
 */
class TargetResponse extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(42);
        $this->attach('rank', new U32);
        $this->attach('targets', new Set(new Target));
    }
}

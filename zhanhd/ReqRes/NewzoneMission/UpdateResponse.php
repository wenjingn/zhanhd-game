<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\NewzoneMission;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set;

/**
 *
 */
class UpdateResponse extends ReqResHeader
{
    /**
     * @return void
     */
    public function setupResponse()
    {
        $this->command->intval(156);
        $this->attach('missions', new Set(new Info));
    }
}

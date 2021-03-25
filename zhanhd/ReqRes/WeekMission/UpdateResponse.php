<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\WeekMission;

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
    protected function setupResponse()
    {
        $this->command->intval(161);
        $this->attach('missions', new Set(new Info));
    }
}

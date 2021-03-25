<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\NewzoneMission\Poll;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set,
    System\ReqRes\Int\U16;

/**
 *
 */
use Zhanhd\ReqRes\NewzoneMission\Info;

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
        $this->command->intval(160);
        $this->attach('day', new U16);
        $this->attach('newzoneMissions', new Set(new Info));
    }
}

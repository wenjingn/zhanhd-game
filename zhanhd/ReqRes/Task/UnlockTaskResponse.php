<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Task;

/**
 *
 */
use System\Swoole\ReqResHeader;

/**
 *
 */
use Zhanhd\ReqRes\TaskInfo;

/**
 *
 */
class UnlockTaskResponse extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(35);
        $this->attach('unlock', new TaskInfo);
    }
}

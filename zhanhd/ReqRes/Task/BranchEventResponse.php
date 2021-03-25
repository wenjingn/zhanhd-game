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
class BranchEventResponse extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(33);
        $this->attach('task', new TaskInfo);
    }
}

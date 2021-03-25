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
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\TaskInfo,
    Zhanhd\ReqRes\RewardInfo;

/**
 *
 */
class ResourceEventResponse extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(18);

        $this->attach('task',   new TaskInfo);
        $this->attach('reward', new RewardInfo);
        $this->attach('exp',    new U32);
    }
}

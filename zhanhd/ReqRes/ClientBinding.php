<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\ReqRes\TaskInfo;

/**
 *
 */
class ClientBinding extends Box
{
    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('lastcmd',      new U16);
        $this->attach('lasttask',     new TaskInfo);
        $this->attach('heartbeat',    new U16);
        $this->attach('maxMessageId', new U64);
        $this->attach('maxRewardId',  new U64);
        $this->attach('counterCycle', new U32);
    }
}

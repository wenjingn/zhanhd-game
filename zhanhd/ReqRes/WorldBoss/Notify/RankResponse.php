<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\WorldBoss\Notify;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

/**
 *
 */
class RankResponse extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(177);
        $this->attach('rank', new U32);
    }
}

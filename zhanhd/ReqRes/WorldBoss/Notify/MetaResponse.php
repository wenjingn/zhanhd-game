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
    System\ReqRes\Int\U64;

/**
 *
 */
class MetaResponse extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(176);
        $this->attach('damage', new U64);
        $this->attach('bosshp', new U64);
    }
}

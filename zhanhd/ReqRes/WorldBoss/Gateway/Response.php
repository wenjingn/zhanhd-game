<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\WorldBoss\Gateway;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

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
        $this->command->intval(175);
        $this->attach('flag',   new U16);
        $this->attach('id',     new U32);
        $this->attach('rank',   new U32);
        $this->attach('damage', new U64);
        $this->attach('bosschp', new U64);
        $this->attach('bossrhp', new U64);
        $this->attach('leftsec', new U32);
    }
}

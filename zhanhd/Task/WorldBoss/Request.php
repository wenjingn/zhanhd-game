<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Task\WorldBoss;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

/**
 *
 */
class Request extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('bosshp', new U64);
        $this->attach('damage', new U64);
        $this->attach('dmgsum', new U64);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\WorldBoss\Attack;

use System\ReqRes\Box,
    System\ReqRes\Int\U16;

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
        $this->attach('gid', new U16);
    }
}

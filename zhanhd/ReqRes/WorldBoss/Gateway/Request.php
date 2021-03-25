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
use System\ReqRes\Box,
    System\ReqRes\Int\U16;

/**
 *
 */
class Request extends Box
{
    /**
     * @const integer
     */
    const FLAG_QUERY = 0;
    const FLAG_ENTER = 1;
    const FLAG_EXIT  = 2;

    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('flag', new U16);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Relation\Confirm;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Set,
    System\ReqRes\Int\U64,
    System\ReqRes\Int\U16;

/**
 *
 */
class Request extends Box
{
    /**
     * @const integer
     */
    const FLAG_REFUSE = 0;
    const FLAG_ACCEPT = 1;

    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('flag',    new U16);
        $this->attach('friends', new Set(new U64));
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Relation\Love;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U64,
    System\ReqRes\Int\U32;

/**
 *
 */
class Request extends Box
{
    /**
     * @const integer
     */
    const FLAG_FREE = 1;
    const FLAG_PAID = 2;

    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('flag', new U16);
        $this->attach('fid',  new U64);
    }
}

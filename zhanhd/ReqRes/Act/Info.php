<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Act;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32;

/**
 *
 */
class Info extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('type',    new U16);
        $this->attach('begin',   new U32);
        $this->attach('end',     new U32);
        $this->attach('leftSec', new U32);
    }
}

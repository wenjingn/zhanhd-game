<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\DayIns;

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
     * @return void
     */
    protected function initial()
    {
        $this->attach('iid',   new U16);
        $this->attach('diff',  new U16);
        $this->attach('gid',   new U16);
    }
}

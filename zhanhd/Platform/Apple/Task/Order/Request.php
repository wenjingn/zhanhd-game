<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Platform\Apple\Task\Order;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U64,
    System\ReqRes\Str;

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
        $this->attach('pid',    new U64);
        $this->attach('serial', new Str);
    }
}

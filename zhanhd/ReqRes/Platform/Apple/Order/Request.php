<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Platform\Apple\Order;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32,
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
        $this->attach('merchandise', new U32);
        $this->attach('orderSerial', new Str);
    }
}

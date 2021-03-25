<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Platform\Tencent\Order;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32;

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
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Order\Generate;

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

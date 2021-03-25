<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Store\Prop;

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
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('gid', new U32);
    }
}

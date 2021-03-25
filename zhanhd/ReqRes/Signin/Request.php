<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Signin;

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
    const FLAG_SIGNIN  = 1;
    const FLAG_GREENER = 0;

    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('flag', new U16);
    }
}

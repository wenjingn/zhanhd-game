<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Account\AutoSignin;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Str,
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
        $this->attach('secret', new Str);
        $this->attach('zone',   new U16);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Platform\AY\Login;

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
        $this->attach('accountid', new Str);
        $this->attach('sessionid', new Str);
        $this->attach('zone', new U16);
    }
}

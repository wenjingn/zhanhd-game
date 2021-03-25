<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Platform\Baidu\Login;

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
        $this->attach('uid', new Str);
        $this->attach('accessToken', new Str);
        $this->attach('zone', new U16);
    }
}

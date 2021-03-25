<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Platform\Tencent\Login;

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
        $this->attach('zone',         new U16);
        $this->attach('platform',     new U16);
        $this->attach('openid',       new Str);
        $this->attach('accessToken',  new Str);
        $this->attach('pf',           new Str);
        $this->attach('pfkey',        new Str);
        $this->attach('anotherToken', new Str);
    }
}

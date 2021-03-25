<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Platform\Lezhuo\Login;

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
        $this->attach('zone',       new U16);
        $this->attach('appvers',    new Str);
        $this->attach('token',      new Str);
        $this->attach('device',     new Str);
        $this->attach('deviceuuid', new Str);
        $this->attach('mixcode',    new Str);
        $this->attach('os',         new Str);
        $this->attach('osvers',     new Str);
        $this->attach('from',       new Str);
        $this->attach('cpscid',     new Str);
    }
}

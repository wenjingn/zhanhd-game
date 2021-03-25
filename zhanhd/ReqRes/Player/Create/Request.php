<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Player\Create;

/**
 *
 */
use System\ReqRes\Box,
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
        $this->attach('nick', new Str);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Chat;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U64,
    System\ReqRes\Str;

/**
 *
 */
class Request extends Box
{
    /**
     * @command code 164
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('channel', new U16);
        $this->attach('to',      new U64);
        $this->attach('content', new Str);
    }
}

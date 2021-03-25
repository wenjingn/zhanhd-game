<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild\Create;

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
        $this->attach('name', new Str);
    }
}

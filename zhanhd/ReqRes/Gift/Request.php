<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Gift;

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
        $this->attach('serial', new Str);
    }
}

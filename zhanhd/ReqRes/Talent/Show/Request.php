<?php
/**
 * $%Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Talent\Show;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Set,
    System\ReqRes\Int\U64,
    System\ReqRes\Int\U32;

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
        $this->attach('talents', new Set(new U64));
    }
}

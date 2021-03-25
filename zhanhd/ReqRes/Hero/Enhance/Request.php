<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Hero\Enhance;

/**
 *
 */
use System\ReqRes\Int\U32,
    System\ReqRes\Int\U64,
    System\ReqRes\Set,
    System\ReqRes\Box;

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
        $this->attach('peid', new U64);
        $this->attach('consumptions', new Set(new U64));
    }
}

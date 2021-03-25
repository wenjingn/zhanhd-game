<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Equip\Decompose;

/**
 *
 */
use System\ReqRes\Set,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64,
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
        $this->attach('peids', new Set(new U64));
    }
}

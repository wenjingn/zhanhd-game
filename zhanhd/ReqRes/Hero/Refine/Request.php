<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Hero\Refine;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U64;

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
    }
}

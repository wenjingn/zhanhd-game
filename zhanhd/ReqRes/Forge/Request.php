<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Forge;

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
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('peid', new U64);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Marry;

/**
 *
 */
use System\ReqRes\Box,
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
        $this->attach('peid', new U64);
    }
}

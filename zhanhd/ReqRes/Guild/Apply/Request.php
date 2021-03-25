<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild\Apply;

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
        $this->attach('gid', new U64);
    }
}

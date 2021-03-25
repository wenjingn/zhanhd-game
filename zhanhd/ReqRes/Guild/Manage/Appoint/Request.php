<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild\Manage\Appoint;

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
        $this->attach('pid', new U64);
    }
}

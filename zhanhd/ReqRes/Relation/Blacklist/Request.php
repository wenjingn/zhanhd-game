<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Relation\Blacklist;

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
     * @command code 166
     * 
     * @return void
     */
    protected function initial()
    {
        $this->attach('pid', new U64);
    }
}

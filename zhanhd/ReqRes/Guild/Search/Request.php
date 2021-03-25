<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild\Search;

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
        $this->attach('keyword', new Str);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Relation\Recommend;

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
        $this->attach('searchFields', new Str);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\FixedTimeProp;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16;

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
        $this->attach('id', new U16);
    }
}

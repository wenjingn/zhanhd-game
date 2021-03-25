<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild\Gift;

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
     * @return void
     */
    protected function initial()
    {
        $this->attach('gift', new U16);
    }
}

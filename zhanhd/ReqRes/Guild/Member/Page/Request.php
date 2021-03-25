<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild\Member\Page;

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
        $this->attach('index', new U16);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Rob\Tarlist;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U08;

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
        $this->attach('flag', new U08);
    }
}

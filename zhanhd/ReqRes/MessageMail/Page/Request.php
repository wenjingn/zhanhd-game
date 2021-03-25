<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\MessageMail\Page;

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
        $this->attach('page', new U16);
        $this->attach('num',  new U16);
    }
}

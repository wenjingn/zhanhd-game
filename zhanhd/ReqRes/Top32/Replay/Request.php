<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Top32\Replay;

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
        $this->attach('index', new U08);
    }
}

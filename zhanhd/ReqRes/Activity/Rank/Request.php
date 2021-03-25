<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Activity\Rank;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32;

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
        $this->attach('aid', new U32);
    }
}

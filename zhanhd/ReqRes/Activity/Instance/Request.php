<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Activity\Instance;

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
        $this->attach('aid',  new U32);
        $this->attach('flag', new U32);
    }
}

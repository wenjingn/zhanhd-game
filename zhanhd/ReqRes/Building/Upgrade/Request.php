<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Building\Upgrade;

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
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('bid', new U32);
    }
}

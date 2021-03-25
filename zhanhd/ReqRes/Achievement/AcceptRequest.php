<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Achievement;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32;

/**
 *
 */
class AcceptRequest extends Box
{
    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('aid', new U32);
    }
}

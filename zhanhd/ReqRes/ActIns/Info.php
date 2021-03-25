<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\ActIns;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16;

/**
 *
 */
class Info extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('aid', new U16);
        $this->attach('floor', new U16);
    }
}

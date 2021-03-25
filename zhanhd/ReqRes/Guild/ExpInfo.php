<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32;

/**
 *
 */
class ExpInfo extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('exp', new U32);
        $this->attach('lvl', new U16);
    }
}

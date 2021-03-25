<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Combat;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32;

/**
 *
 */
class Damage extends Box
{
    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('ext', new U16);
        $this->attach('sym', new U16);
        $this->attach('dmg', new U32);
    }
}

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
    System\ReqRes\Int\U16;

/**
 *
 */
class Effect extends Box
{
    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('eid',  new U16);
        $this->attach('flag', new U16);
    }
}

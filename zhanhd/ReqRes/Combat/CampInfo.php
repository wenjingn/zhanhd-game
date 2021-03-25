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
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

/**
 *
 */
class CampInfo extends Box
{
    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('camp', new U16);
        $this->attach('eid',  new U64);
    }
}

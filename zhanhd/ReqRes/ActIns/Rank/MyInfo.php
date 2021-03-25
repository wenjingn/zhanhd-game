<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\ActIns\Rank;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16;

/**
 *
 */
class MyInfo extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('rank',  new U16);
        $this->attach('floor', new U16);
    }
}

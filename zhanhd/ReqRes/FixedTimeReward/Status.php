<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\FixedTimeReward;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16;

/**
 *
 */
class Status extends Box
{
    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('id',   new U16);
        $this->attach('flag', new U16);
    }
}

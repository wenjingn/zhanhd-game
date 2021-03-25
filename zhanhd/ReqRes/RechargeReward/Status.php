<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\RechargeReward;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32;

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
        $this->attach('id', new U32);
        $this->attach('r',  new U16);
        $this->attach('a',  new U16);
    }
}

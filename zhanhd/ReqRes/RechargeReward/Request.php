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
    System\ReqRes\Int\U32;

/**
 *
 */
class Request extends Box
{
    /**
     * @command code 164
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('id', new U32);
    }
}

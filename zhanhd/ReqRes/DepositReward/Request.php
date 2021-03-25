<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\DepositReward;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16;

/**
 *
 */
class Request extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('drid', new U16);
    }
}

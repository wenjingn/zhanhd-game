<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\RewardMail\Receive;

/**
 *
 */
use System\ReqRes\Set,
    System\ReqRes\Box,
    System\ReqRes\Int\U64;

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
        $this->attach('rewardIds', new Set(new U64));
    }
}

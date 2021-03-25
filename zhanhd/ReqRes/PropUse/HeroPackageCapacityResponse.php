<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\PropUse;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

/**
 *
 */
class HeroPackageCapacityResponse extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(88);
        $this->attach('capacity', new U32);
    }
}

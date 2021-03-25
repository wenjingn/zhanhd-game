<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Achievement;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\RewardInfo;

/**
 *
 */
class AcceptResponse extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(76);

        $this->attach('aid',     new U32);
        $this->attach('rewards', new RewardInfo);
    }
}

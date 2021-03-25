<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\RewardMail;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set;

/**
 *
 */
use Zhanhd\ReqRes\RewardMailInfo;

/**
 *
 */
class NotifyResponse extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(110);
        $this->attach('mails', new Set(new RewardMailInfo));
    }
}

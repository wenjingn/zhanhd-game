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
    System\ReqRes\Set;

/**
 *
 */
use Zhanhd\ReqRes\AchievementInfo;

/**
 *
 */
class UpdateResponse extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(74);
        $this->attach('notify', new Set(new AchievementInfo));
    }
}

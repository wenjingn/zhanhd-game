<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Crusade\Reward;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\RewardInfo;

/**
 *
 */
class Response extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(39);

        $this->attach('cid', new U32);
        $this->attach('aid', new U32);
        $this->attach('seq', new U32);
        $this->attach('gid', new U32);
        $this->attach('win', new U16);
        $this->attach('exp', new U32);

        $this->attach('reward', new RewardInfo);
    }
}

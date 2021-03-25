<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Activity\Rank;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\Activity\RankInfo;

/**
 *
 */
class Response extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(92);
        $this->attach('aid',      new U32);
        $this->attach('rankSelf', new RankInfo);
        $this->attach('rankList', new Set(new RankInfo));
    }
}

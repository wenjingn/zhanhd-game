<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqREs\Top32\Poll;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set;

/**
 *
 */
use Zhanhd\ReqRes\Top32\Info,
    Zhanhd\ReqRes\Top32\CompetitionInfo;

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
        $this->command->intval(240);
        $this->attach('champion', new Info);
        $this->attach('status', new Set(new CompetitionInfo));
    }
}

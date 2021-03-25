<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Rob\Tarlist;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set,
    System\ReqRes\Int\U08,
    System\ReqRes\Int\U16;

/**
 *
 */
use Zhanhd\ReqRes\Rob\Target;

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
        $this->command->intval(244);
        $this->attach('targets', new Set(new Target));
        $this->attach('refreshCD', new U16);
        $this->attach('robSuccessTimes', new U08);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\ActIns\Rank;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set;

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
        $this->command->intval(149);
        $this->attach('idaily', new MyInfo);
        $this->attach('iweekly',new MyInfo);
        $this->attach('daily',  new Set(new Info));
        $this->attach('weekly', new Set(new Info));
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Relation\Love;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64,
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
        $this->command->intval(69);
        $this->attach('fid',   new U64);
        $this->attach('love',  new U32);
        $this->attach('cd',    new U32);
        $this->attach('times', new U16);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Act\DiaRec;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set,
    System\ReqRes\Int\U16;

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
        $this->command->intval(236);
        $this->attach('type', new U16);
        $this->attach('times', new U16);
        $this->attach('status', new Set(new Info));
    }
}

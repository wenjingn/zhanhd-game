<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Equip\Decompose;

/**
 *
 */
use System\ReqRes\Set,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64,
    System\Swoole\ReqResHeader;

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
        $this->command->intval(59);
        $this->attach('peids', new Set(new U64));
    }
}

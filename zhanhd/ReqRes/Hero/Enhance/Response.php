<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Hero\Enhance;

/**
 *
 */
use System\ReqRes\Int\U08,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64,
    System\ReqRes\Set,
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
        $this->command->intval(57);
        $this->attach('peid', new U64);
        $this->attach('skillLevels', new Set(new U08));
        $this->attach('consumptions', new Set(new U64));
    }
}

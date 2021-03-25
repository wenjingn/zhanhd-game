<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Hero\Upgrade;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set,
    System\ReqRes\Int\U64,
    System\ReqRes\Int\U32,
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
        $this->command->intval(114);
        $this->attach('peid', new U64);
        $this->attach('exp', new U32);
        $this->attach('lvl', new U16);
        $this->attach('expcardNum', new U32);
        $this->attach('eliminated', new Set(new U64));
    }
}

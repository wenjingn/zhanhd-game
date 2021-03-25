<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Relation\Blacklist\Remove;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Set,
    System\ReqRes\Int\U64;

/**
 *
 */
class Request extends Box
{
    /**
     * @command code 168
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('pids', new Set(new U64));
    }
}

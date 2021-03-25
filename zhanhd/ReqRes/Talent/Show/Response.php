<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Talent\Show;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U64,
    Zhanhd\ReqRes\Entity\Hero;

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
        $this->command->intval(73);
        $this->attach('eliminated', new Set(new U64));
        $this->attach('heros', new Set(new Hero));
        $this->attach('times', new U16);
    }
}

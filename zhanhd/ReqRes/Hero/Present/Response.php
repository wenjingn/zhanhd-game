<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Hero\Present;

/**
 *
 */
use System\Swoole\ReqResHeader,
    SYstem\ReqRes\Set,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\ReqRes\Entity\Prop;

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
        $this->attach('peid', new U64);
        $this->attach('love', new U16);
        $this->attach('props', new Set(new Prop));
    }
}

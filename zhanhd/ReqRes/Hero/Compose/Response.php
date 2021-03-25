<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Hero\Compose;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\Entity\Hero;

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
        $this->command->intval(137);
        $this->attach('eid', new U32);
        $this->attach('num', new U32);
        $this->attach('hero', new Hero);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Recruit;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\Entity\Hero;

/**
 *
 */
class HeroResponse extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(13);
        $this->attach('retval', new Set(new Hero));
        $this->attach('freeCD', new U32);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Relation\LoveReward;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64,
    Zhanhd\ReqRes\Entity\Entity;

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
        $this->command->intval(71);
        $this->attach('fid',    new U64);
        $this->attach('love',   new U32);
        $this->attach('gear',   new U32);
        $this->attach('entity', new Entity);
    }
}

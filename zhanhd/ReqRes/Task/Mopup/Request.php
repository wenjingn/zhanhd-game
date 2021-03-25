<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Task\Mopup;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32;

/**
 *
 */
class Request extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('diff',    new U32);
        $this->attach('gid',     new U32);
        $this->attach('dynasty', new U32);
        $this->attach('battle',  new U32);
    }

    /**
     * @return integer
     */
    public function getBattleId()
    {
        return $this->dynasty->intval() * 100 + $this->battle->intval();
    }
}

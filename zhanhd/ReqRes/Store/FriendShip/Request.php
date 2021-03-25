<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Store\FriendShip;

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
        $this->attach('version', new U32);
        $this->attach('gid',     new U32);
    }
}

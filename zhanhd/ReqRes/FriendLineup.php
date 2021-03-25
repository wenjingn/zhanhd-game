<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Set,
    System\ReqRes\Int\U32;

/**
 *
 */
class FriendLineup extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('formation', new U32);
        $this->attach('eids',   new Set(new U32));
        $this->attach('levels', new Set(new U32));
    }
}

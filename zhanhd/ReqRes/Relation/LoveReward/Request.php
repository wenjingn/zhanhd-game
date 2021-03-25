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
use System\ReqRes\Box,
    System\ReqRes\Int\U64,
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
        $this->attach('fid',  new U64);
        $this->attach('gear', new U32);
    }
}

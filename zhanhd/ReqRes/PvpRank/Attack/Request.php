<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\PvpRank\Attack;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

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
        $this->attach('pid',  new U64);
        $this->attach('rank', new U32);
    }
}

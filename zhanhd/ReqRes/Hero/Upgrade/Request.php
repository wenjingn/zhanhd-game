<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Hero\Upgrade;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Set,
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
        $this->attach('peid', new U64);
        $this->attach('expcardNum',   new U32);
        $this->attach('eliminatings', new Set(new U64));
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Crusade\Attack;

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
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('gid', new U32);
        $this->attach('cid', new U32);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\PropUse;

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
        $this->attach('propId', new U32);
        $this->attach('num',    new U32);
        $this->attach('gid',    new U32);
    }
}

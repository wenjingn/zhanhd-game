<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Fragment;

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
        $this->attach('frag', new U32);
        $this->attach('num',  new U32);
    }
}

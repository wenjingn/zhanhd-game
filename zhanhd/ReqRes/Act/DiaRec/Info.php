<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Act\DiaRec;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16;

/**
 *
 */
class Info extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('rid', new U16);
        $this->attach('got', new U16);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Act\DiaRec\Reward;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16;

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
        $this->attach('rid', new U16);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace System\Swoole;

/**
 *
 */
use System\ReqRes\ReqResInterface,
    System\ReqRes\Box,
    System\ReqRes\Int\U16;

/**
 *
 */
class Task extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('code', new U16);
    }

    /**
     * @param integer $c
     * @param ReqResInterface $r
     * @return void
     */
    public function setup($c, ReqResInterface $r = null)
    {
        $this->code->intval($c);
        if ($r) {
            $this->attach('data', $r);
        }
    }
}

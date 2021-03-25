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
    System\ReqRes\Int\U64;

/**
 *
 */
class Pipe extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('pid', new U64);
    }

    /**
     * @param integer $pid
     * @param ReqResInterface $r
     * @return void
     */
    public function setup($pid, ReqResInterface $r)
    {
        $this->pid->intval($pid);
        $this->attach('data', $r);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Task\Guild;

/**
 *
 */
use System\ReqRes\ReqResInterface,
    System\ReqRes\Box,
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
        $this->attach('gid', new U64);
    }

    /**
     * @param integer         $gid
     * @param ReqResInterface $reqres
     * @return void
     */
    public function setup($gid, ReqResInterface $reqres = null)
    {
        $this->gid->intval($gid);
        if ($reqres) {
            $this->attach('data', $reqres);
        }
    }
}

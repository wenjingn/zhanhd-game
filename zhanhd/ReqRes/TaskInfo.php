<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32;

/**
 *
 */
class TaskInfo extends Box
{
    /**
     *
     * @param  integer $tid
     * @return void
     */
    public function setTaskId($tid)
    {
        $this->did->intval((integer) ($tid % 100000000 / 1000000));
        $this->bid->intval((integer) ($tid % 1000000   / 10000));
        $this->fid->intval((integer) ($tid % 10000     / 100));
        $this->eid->intval((integer) ($tid % 100));
    }

    /**
     *
     * @return integer
     */
    public function getTaskId()
    {
        return $this->did->intval() * 1000000 +
               $this->bid->intval() * 10000   +
               $this->fid->intval() * 100     +
               $this->eid->intval();
    }

    /**
     * @param integer $fid
     * @return void
     */
    public function setFightId($fid)
    {
        $this->did->intval((integer) ($fid % 1000000 / 10000));
        $this->bid->intval((integer) ($fid % 10000   / 100));
        $this->fid->intval((integer) ($fid % 100));
    }

    /**
     * @return integer
     */
    public function getFightId()
    {
        return $this->did->intval() * 10000 +
               $this->bid->intval() * 100   +
               $this->fid->intval();
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('did',  new U32);
        $this->attach('bid',  new U32);
        $this->attach('fid',  new U32);
        $this->attach('eid',  new U32);
        $this->attach('flag', new U32);
    }
}

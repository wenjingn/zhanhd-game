<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Leader;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\Object\Player as Owner;

/**
 *
 */
class Image extends Box
{
    /**
     *
     * @return void
     * @todo   setup flags
     */
    public function fromOwnerObject(Owner $o)
    {
        $this->hair   ->intval($o->profile->hair    ?: 1);
        $this->face   ->intval($o->profile->face    ?: 1);
        $this->clothes->intval($o->profile->clothes ?: 1);
        $this->hairclr->intval($o->profile->hairclr ?: 1);
        $this->faceclr->intval($o->profile->faceclr ?: 1);
        $this->eyeclr ->intval($o->profile->eyeclr  ?: 1);
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('hair',     new U32);
        $this->attach('face',     new U32);
        $this->attach('clothes',  new U32);
        $this->attach('hairclr',  new U32);
        $this->attach('faceclr',  new U32);
        $this->attach('eyeclr',   new U32);
    }
}

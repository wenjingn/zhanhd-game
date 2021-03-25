<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Entity;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32;

/**
 *
 */
class Soul extends Box
{
    /**
     * @return void
     */
    public function initial()
    {
        $this->attach('eid', new U32);
        $this->attach('num', new U32);
    }

    /**
     * @param Object $o ['e'=>Entity, 'n'=>integer]
     * @return void
     */
    public function fromObject($o)
    {
        $this->eid->intval($o->e->id);
        $this->num->intval($o->n);
    }
}

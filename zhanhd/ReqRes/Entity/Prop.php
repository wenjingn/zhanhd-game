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
class Prop extends Box
{
    /**
     *
     * @param  Object $o
     * @return void
     * @see Player::increaseEntity
     * @use recruit
     */
    public function fromPlayerEntityObject($o)
    {
        $this->eid->intval($o->e->id);
        $this->num->intval($o->n);
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('eid', new U32);
        $this->attach('num', new U32);
    }
}

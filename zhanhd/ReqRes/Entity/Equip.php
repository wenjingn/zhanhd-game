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
    System\ReqRes\Int\U16;

/**
 *
 */
use Zhanhd\Object\Player\Entity as PlayerEntity;

/**
 *
 */
class Equip extends Box
{
    /**
     *
     * @return void
     */
    public function fromPlayerEntityObject(PlayerEntity $pe)
    {
        $this->pe->peid->intval($pe->id);
        $this->pe->eid ->intval($pe->eid);

        $this->type->intval($pe->e->type);
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('pe',   new Entity);
        $this->attach('type', new U16);
    }
}

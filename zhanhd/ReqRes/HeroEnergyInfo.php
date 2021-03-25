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
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\Object\Player\Entity as PlayerEntity;

/**
 *
 */
class HeroEnergyInfo extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('peid',   new U64);
        $this->attach('energy', new U16);
    }

    /**
     * @param PlayerEntity $pe
     * @param Object $global
     * @return void
     */
    public function fromPlayerEntityObject(PlayerEntity $pe, $global)
    {
        $this->peid->intval($pe->id);
        $this->energy->intval($pe->property->getEnergy($global->ustime));
    }
}

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
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\Object\Player\Entity as PlayerEntity;

/**
 *
 */
class HeroExpInfo extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('peid',   new U64);
        $this->attach('lvl',    new U16);
        $this->attach('exp',    new U16);
        $this->attach('energy', new U16);
    }

    /**
     * @return void
     */
    public function fromPlayerEntityObject(PlayerEntity $pe, $global)
    {
        $this->peid   ->intval($pe->id);
        $this->lvl    ->intval($pe->lvl);
        $this->exp    ->intval($pe->exp);
        $this->energy ->intval($pe->property->getEnergy($global->ustime));
    }
}

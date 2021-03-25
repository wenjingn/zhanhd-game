<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Combat;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\Extension\Combat\Combatant as CombatantObject;

/**
 *
 */
class Combatant extends Box
{
    /**
     *
     * @param  CombatantObject $combatant
     * @return void
     */
    public function fromCombatantObject(CombatantObject $combatant)
    {
        $this->hero->fromCombatantObject($combatant);

        $this->army->intval($combatant->plh->pe->property->aid);
        $this->chpt->intval($combatant->getHpoint());
        $this->rhpt->intval($combatant->getRawHpoint());
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('hero', new HeroInfo);
        $this->attach('army', new U32);
        $this->attach('chpt', new U32); // cur-hpoint
        $this->attach('rhpt', new U32); // raw-hpoint
    }
}

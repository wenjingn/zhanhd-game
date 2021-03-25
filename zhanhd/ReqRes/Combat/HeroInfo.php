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
    System\ReqRes\Set;

/**
 *
 */
use Zhanhd\ReqRes\LineupInfo,
    Zhanhd\Extension\Combat\Combatant as CombatantObject;

/**
 *
 */
class HeroInfo extends Box
{
    /**
     *
     * @param  CombatantObject $combatant
     * @return void
     */
    public function fromCombatantObject(CombatantObject $combatant, array $runtimeEffects = [])
    {
        $this->lineup->camp->intval($combatant->getCamp());
        $this->lineup->fromPlayerLineupHeroObject($combatant->plh);

        $effects = $combatant->getEffects();
        foreach ($runtimeEffects as $eid => $flag) {
            if ($flag) {
                $effects[$eid]['+'] = true;
            } else {
                $effects[$eid]['-'] = true;
            }
        }

        $this->effects->resize(array_reduce($effects, function($n, $flags) {
            return $n + count($flags);
        }, 0));

        $i = 0; foreach ($effects as $eid => $flags) {
            foreach ($flags as $flag => $null) {
                switch ($flag) {
                case '+': $flag = 1; break;
                case '-': $flag = 0; break;
                }

                $this->effects->get($i)->eid ->intval($eid);
                $this->effects->get($i)->flag->intval($flag);
                $i++;
            }
        }
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('lineup',  new LineupInfo);
        $this->attach('effects', new Set(new Effect));
    }
}

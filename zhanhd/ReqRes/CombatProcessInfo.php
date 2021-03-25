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
    System\ReqRes\Set,
    System\ReqRes\Int\U16;

/**
 *
 */
class CombatProcessInfo extends Box
{
    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('win', new U16);
        $this->attach('attacker', new Set(new Combat\Combatant));
        $this->attach('defender', new Set(new Combat\Combatant));
        $this->attach('sequence', new Set(new Combat\Sequence));
    }
}

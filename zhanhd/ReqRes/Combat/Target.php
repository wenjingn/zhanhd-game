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
use System\Stdlib\Object,
    System\ReqRes\Box,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\LineupInfo;

/**
 *
 */
class Target extends Box
{
    /**
     *
     * @return void
     */
    public function fromObject(Object $o)
    {
        $this->lineup->camp->intval($o->combatant->getCamp());
        $this->lineup->fromPlayerLineupHeroObject($o->combatant->plh);

        $this->dmg->ext->intval($o->flags);
        $this->dmg->dmg->intval($o->damage);
        // $this->dmg->sym->intval(0);

        $this->chpt->intval($o->hpoint);
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('lineup', new LineupInfo);
        $this->attach('dmg',    new Damage);
        $this->attach('chpt',   new U32);
    }
}

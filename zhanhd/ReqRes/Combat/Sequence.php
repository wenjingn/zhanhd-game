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
    System\ReqRes\Set,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\LineupInfo;

/**
 *
 */
class Sequence extends Box
{
    /**
     *
     * @return void
     */
    public function fromObject(Object $o)
    {
        $effectHelpers = array();
        if ($o->effects->count()) {
            $effectHelpers[$o->combatant->plh->pe->id] = $o->effects->export();
        }

        $this->sid->intval($o->skill);

        $this->lineup->camp->intval($o->combatant->getCamp());
        $this->lineup->fromPlayerLineupHeroObject($o->combatant->plh);

        $this->targets->resize($o->involved->count());
        foreach ($o->involved as $i => $x) {
            $this->targets->get($i)->fromObject($x);
            if ($x->effects->count()) {
                $effectHelpers[$x->combatant->plh->pe->id] = $x->effects->export();
            }
        }

        $this->heros->resize($o->queue->count());
        $this->queue->resize($o->queue->count());
        foreach ($o->queue as $i => $x) {
            $this->heros->get($i)->fromCombatantObject($x, isset($effectHelpers[$x->plh->pe->id]) ? $effectHelpers[$x->plh->pe->id] : array());

            $this->queue->get($i)->camp->intval($x->getCamp());
            $this->queue->get($i)->eid ->intval($x->plh->pe->eid);
        }
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('sid',     new U32);
        $this->attach('lineup',  new LineupInfo);
        $this->attach('targets', new Set(new Target));
        $this->attach('heros',   new Set(new HeroInfo));
        $this->attach('queue',   new Set(new CampInfo));
    }
}

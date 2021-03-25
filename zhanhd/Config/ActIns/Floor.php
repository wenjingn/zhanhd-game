<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Config\ActIns;

/**
 *
 */
use System\Object\ConfigObject;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Object\Player\Lineup,
    Zhanhd\Object\Player\Lineup\Hero;

/**
 *
 */
class Floor extends ConfigObject
{
    /**
     * @struct self
     *
     * @fmt integer
     * @npc [(integer)pos => Npc]
     * @drop[(integer)eid => (integer)num]
     */

    /**
     * @struct ActInsFloorNpc
     *
     * @eid integer
     * @lvl integer
     * @ehc integer
     */

    /**
     * @return Lineup
     */
    public function getNpcLineup()
    {
        $l = new Lineup;
        $l->gid = 1;
        $l->fid = $this->fmt;
        if ($l->fid) {
            $l->f = Store::get('formation', $l->fid);
        }

        foreach ($this->npc as $pos => $npc) {
            if (null === ($e = Store::get('entity', $npc->eid))) {
                continue;
            }
            $pe = $e->toPe($npc->lvl, $npc->ehc);
            $pe->id = PHP_INT_MAX - $pos;
            $pe->pid = PHP_INT_MAX;

            $h = new Hero;
            $h->pe = $pe;
            $h->gid = 1;
            $h->pos = $pos;
            $h->peid = $pe->id;
            $l->heros->set(null, $h);
        }

        return $l;
    }

    /**
     * @return array
     */
    public function drop()
    {
        if (empty($this->drop)) {
            return [];
        }
        return $this->drop;
    }
}

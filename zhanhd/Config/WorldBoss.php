<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Config;

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
class WorldBoss extends ConfigObject
{
    /**
     * @struct self
     * @id  integer
     * @lvl integer
     * @ehc integer
     */

    /**
     * @param integer $lvl
     * @return Lineup
     */
    public function getNpcLineup($lvl)
    {
        if (null === ($e = Store::get('entity', $this->id))) {
            throw new Exception('notfound worldboss');
        }

        $l = new Lineup;
        $l->gid = 1;
        $l->fid = 0;
        
        $pe = $e->toPe($lvl, $this->ehc);
        $pe->id = PHP_INT_MAX - 4;
        $pe->pid = PHP_INT_MAX;
        
        $h = new Hero;
        $h->pe = $pe;
        $h->gid = 1;
        $h->pos = 4;
        $h->peid = $pe->id;
        $l->heros->set(null, $h);
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

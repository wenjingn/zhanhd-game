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
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\Object\Player\Lineup\Hero as PlayerLineupHero,
    Zhanhd\ReqRes\Entity\Entity;

/**
 *
 */
class LineupInfo extends Box
{
    /**
     *
     * @param  PlayerLineupHero $plh
     * @return void
     */
    public function fromPlayerLineupHeroObject(PlayerLineupHero $plh)
    {
        $this->pos->intval($plh->pos);

        $this->pe->forge->intval($plh->pe->lvl);
        $this->pe->peid->intval($plh->pe->id);
        $this->pe->eid ->intval($plh->pe->eid);
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('camp', new U32);
        $this->attach('pos',  new U32);

        $this->attach('pe', new Entity);
    }
}

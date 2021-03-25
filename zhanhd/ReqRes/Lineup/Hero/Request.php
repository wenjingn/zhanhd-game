<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Lineup\Hero;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Set,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\LineupInfo;

/**
 *
 */
class Request extends Box
{
    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('gid',     new U32);
        $this->attach('fid',     new U32);
        $this->attach('lineups', new Set(new LineupInfo));
    }

    /**
     * @param PlayerLineup $pl
     * @return void
     */
    public function fromObject($pl)
    {
        $this->gid->intval($pl->gid);
        $this->fid->intval($pl->fid);
        $this->lineups->resize($pl->heros->count());
        foreach ($pl->heros as $i => $plh) {
            $this->lineups->get($i)->fromPlayerLineupHeroObject($plh);
        }
    }
}

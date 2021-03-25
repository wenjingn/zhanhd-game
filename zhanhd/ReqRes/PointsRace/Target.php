<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\PointsRace;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U08,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\PvpRank\Target as PvpRankTarget,
    Zhanhd\Object\Player,
    Zhanhd\Object\PointsRace\Target as PointsRaceTarget;

/**
 *
 */
class Target extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('p', new PvpRankTarget);
        $this->attach('c', new U32);
        $this->attach('r', new U08);
    }

    /**
     * @param PointsRaceTarget $target
     * @return void
     */
    public function fromObject(PointsRaceTarget $target, $score)
    {
        $p = new Player;
        $p->find($target->tid);
        $this->p->fromPlayerObject($p, $score);
        $this->c->intval($p->getLineup(1)->captain);
        $this->r->intval($target->status);
    }
}

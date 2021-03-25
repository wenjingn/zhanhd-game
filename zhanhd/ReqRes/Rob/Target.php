<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Rob;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\PvpRank\Target as PvpRankTarget,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Rob\Target as RobTarget,
    Zhanhd\Extension\Rob;

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
        $this->attach('r', new U16);
        $this->attach('n', new U16);
    }

    /**
     * @param RobTarget $target
     * @param integer $power
     * @return void
     */
    public function fromObject(RobTarget $target, $power)
    {
        $p = new Player;
        $p->find($target->tid);
        $this->p->fromPlayerObject($p, 0);
        $l = $p->getLineup(1);
        $this->c->intval($l->captain);
        $this->r->intval($target->status);
        $robResource = $p->robResource();
        $robResource = Rob::robResourceAddition($robResource, $power, $l->power);
        $this->n->intval(array_sum($robResource));
    }
}

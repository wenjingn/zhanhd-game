<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Top32;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\PvpRank\Target,
    Zhanhd\Object\Player;

/**
 *
 */
class Info extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('pvptar', new Target);
        $this->attach('captain', new U32);
    }

    /**
     * @param Player $p
     * @param integer $rank
     * @param integer $cap
     * @return void
     */
    public function fromPlayerObject(Player $p, $rank, $cap)
    {
        $this->pvptar->fromPlayerObject($p, $rank);
        $this->captain->intval($cap);
    }
}

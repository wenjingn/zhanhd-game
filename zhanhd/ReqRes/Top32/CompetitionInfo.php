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
    System\ReqRes\Int\U08,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\PvpRank\Target,
    Zhanhd\Object\Player;

/**
 *
 */
class CompetitionInfo extends Box
{
    /**
     * @const integer
     */
    const STATUS_WAIT   = 0;
    const STATUS_FINISH = 1;

    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('index', new U08);
        $this->attach('attacker', new Target);
        $this->attach('defender', new Target);
        $this->attach('attackerCap', new U32);
        $this->attach('defenderCap', new U32);
        $this->attach('status', new U08);
        $this->attach('result', new U08);
    }

    /**
     * @param Player $attacker
     * @param integer $attackerCap
     * @param Player $defender
     * @param integer $defenderCap
     * @return void
     */
    public function fromObject(Player $attacker = null, $attackerCap, Player $defender = null, $defenderCap)
    {
		if ($attacker) {
			$this->attacker->fromPlayerObject($attacker, 0);
			$this->attackerCap->intval($attackerCap);
		}
		if ($defender) {
			$this->defender->fromPlayerObject($defender, 0);
			$this->defenderCap->intval($defenderCap);
		}
    }
}

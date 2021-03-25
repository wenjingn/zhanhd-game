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
use System\ReqRes\Set,
    System\ReqRes\Box,
    System\ReqRes\Int\U16;

/**
 *
 */
use Zhanhd\Object\Player,
    Zhanhd\Object\Player\Counter\Cycle as PlayerCounterCycle;

/**
 *
 */
class QuestionInfo extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('qaRecords',  new Set(new U16));
        $this->attach('timesToday', new U16);
        $this->attach('CONFIG_DAILY_LIMIT', new U16);
        $this->qaRecords->resize(PlayerCounterCycle::DAILY_QA_LIMIT);
        $this->CONFIG_DAILY_LIMIT->intval(PlayerCounterCycle::DAILY_QA_LIMIT);
    }

    /**
     * @param Player $p
     * @return void
     */
    public function fromPlayerObject(Player $p)
    {
        for ($i = 0; $i < $p->counterCycle->qa; $i++) {
            $correct = $p->counterCycle->qaRecords & (1 << $i);
            $this->qaRecords->get($i)->intval($correct ? 1 : 2);
        }
        $this->timesToday->intval($p->counterCycle->qa);
    }
}

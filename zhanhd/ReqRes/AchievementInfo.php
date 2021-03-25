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
use Zhanhd\Config\Achievement,
    Zhanhd\Object\Player                as Owner,
    Zhanhd\Object\Player\Achievement    as PlayerAchievement;

/**
 *
 */
class AchievementInfo extends Box
{
    /**
     *
     * @return void
     */
    public function fromOwnerObject(Achievement $a, Owner $o, PlayerAchievement $pa = null)
    {
        $this->aid->intval($a->id);

        if (null === $pa) {
            $pa = new PlayerAchievement;
            $pa->find($o->id, $a->id);
        }

        switch ($pa->flags) {
        case PlayerAchievement::FLAG_INIT:
            $this->flag->intval(1);
            break;
        case PlayerAchievement::FLAG_DONE:
            $this->flag->intval(2);
            break;
        }

        if ($a->intval) {
            $this->max->intval($a->intval);

            switch ($a->type) {
            case Achievement::TYPE_BRANCH:
                $this->num->intval($o->counter     ->{$a->getCounterKey()});
                break;

            case Achievement::TYPE_CYCLE:
                $this->num->intval($o->counterCycle->{$a->getCounterKey()});
                if ($this->num->intval() < $a->intval) {
                    $this->flag->intval(0);
                }

                break;
            }
        }
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('aid',  new U32);
        $this->attach('flag', new U32);
        $this->attach('num',  new U32);
        $this->attach('max',  new U32);
    }
}

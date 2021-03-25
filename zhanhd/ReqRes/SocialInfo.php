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
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\Object\Player,
    Zhanhd\Object\Player\Relation,
    Zhanhd\Object\Player\Counter\Cycle as PlayerCounterCycle;

/**
 *
 */
class SocialInfo extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('CONFIG_FRIEND_MAX', new U32);
        $this->attach('CONFIG_FRIENDSHIP_MAX', new U16);

        $this->attach('love', new U16);
        $this->attach('CONFIG_LOVE_MAX', new U16);

        $this->attach('paid', new U16);
        $this->attach('CONFIG_LOVEPAID_CARDINAL', new U16);

        $this->attach('lovecd', new U32);

        $this->attach('CONFIG_GEAR_FIRST',  new U16);
        $this->attach('CONFIG_GEAR_SECOND', new U16);
        $this->attach('CONFIG_GEAR_THIRD',  new U16);
        $this->attach('CONFIG_GEAR_FOURTH', new U16);
    }

    /**
     * @param  Player $p
     * @param  Object $global
     * @return void
     */
    public function fromOwnerObject(Player $p, $global)
    {

        $this->love->intval($p->counterCycle->freeLove);
        $this->lovecd->intval($p->recent->getFreeLoveCD($global->ustime));

        $this->CONFIG_FRIEND_MAX->intval(Relation::MAX_LIMIT);
        $this->CONFIG_FRIENDSHIP_MAX->intval(PlayerCounterCycle::DAILY_FRIENDSHIP_LIMIT);
        $this->CONFIG_LOVEPAID_CARDINAL->intval(Relation::PAID_CARDINAL);
        $this->CONFIG_LOVE_MAX   ->intval(PlayerCounterCycle::DAILY_LOVE_LIMIT);
        $this->CONFIG_GEAR_FIRST ->intval(Relation::$gears[1]);
        $this->CONFIG_GEAR_SECOND->intval(Relation::$gears[2]);
        $this->CONFIG_GEAR_THIRD ->intval(Relation::$gears[3]);
        $this->CONFIG_GEAR_FOURTH->intval(Relation::$gears[4]);
    }
}

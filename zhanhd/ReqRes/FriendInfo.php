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
    System\ReqRes\Set,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\Object\Player,
    Zhanhd\Object\Player\Relation            as PlayerRelation,
    Zhanhd\Object\Player\Relation\Love       as PlayerRelationLove,
    Zhanhd\ReqRes\LeaderInfo;

/**
 *
 */
class FriendInfo extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('fid',     new U64); // friend id
        $this->attach('captain', new U64); // captain eid
        $this->attach('hall',    new U32); // hall level
        $this->attach('power',   new U32); // 战力
        $this->attach('login',   new U32); // last login time
        //$this->attach('love',    new U32);
        $this->attach('liked',   new U16);
        //$this->attach('bonus',   new Set(new U32));
        $this->attach('leader',  new LeaderInfo);
        $this->attach('lineup',  new FriendLineup);
    }

    /**
     * @param  PlayerRelation $pr
     * @param  Object $global
     * @return void
     */
    public function fromRelationObject(PlayerRelation $pr, $global)
    {
        $f = new Player;
        $f->find($pr->fid);
        $this->fid->intval($f->id);

        $lineup = $f->getLineup(1);
        $this->captain->intval($lineup->captain);

        $this->hall->intval($f->getBuilding(1)->lvl);

        /* power */
        $this->power->intval($lineup->power);

        $this->login ->intval((integer)($f->lastLogin / 1000000));
        //$this->love  ->intval($pr->loveValue);

        $this->liked->intval((integer)($pr->lastLikedDay() === $global->date));
/*
        $bonus = $pr->getBonus();
        $this->bonus->resize(count(PlayerRelation::$gears));
        foreach ($bonus as $k => $o) {
            if ($o->flag <> PlayerRelationLove::FLAG_RECEIVED) {
                continue;
            }
            $this->bonus->get($k - 1)->intval($o->eid);
        }
*/
        $this->leader->fromPlayerObject($f);

        if ($pr->hasflags(PlayerRelation::FLAG_FRIEND)) {
            $this->lineup->formation->intval($lineup->fid);
            $this->lineup->eids->resize(6);
            $this->lineup->levels->resize(6);
            foreach ($lineup->heros as $o) {
                $this->lineup->eids->get($o->pos)->intval($o->pe->eid);
                $this->lineup->levels->get($o->pos)->intval($o->pe->lvl);;
            }
        }
    }
}

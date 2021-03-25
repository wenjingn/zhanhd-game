<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension;

/**
 *
 */
use System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Object\Player,
    Zhanhd\Config\Entity                   as SourceEntity,
    Zhanhd\Config\NewzoneMission,
    Zhanhd\Config\WeekMission,
    Zhanhd\Config\Activity,
    Zhanhd\Extension\NewzoneMission\Module as NewzoneMissionModule,
    Zhanhd\Extension\Achievement\Module    as AchievementModule,
    Zhanhd\Extension\WeekMission\Module    as WeekMissionModule,
    Zhanhd\Extension\Activity\Module       as ActivityModule;

/**
 *
 */
class TaskEmitter
{
    /**
     * @param Player $p
     * @param Global $g
     * @param Object $o
     * @return void
     */
    public static function increaseEntity($p, $g, $o)
    {
        if ($o->ignoreAchievement) return;
        if ($o->e->type == SourceEntity::TYPE_HERO) {
            $p->counterCycle->heroGain += $o->n;
            $p->counter->heroGain += $o->n;
            switch ($o->e->rarity) {
            case 4:
                $p->counterCycle->hero4starGain += $o->n;
                $p->counter->hero4starGain += $o->n;
                break;
            case 5:
                $p->counterCycle->hero5starGain += $o->n;
                $p->counter->hero5starGain += $o->n;
                break;
            }
            NewzoneMissionModule::trigger($p, $g, NewzoneMission::TYPE_HEROGAIN, $o->e->rarity);
            (new AchievementModule($p))->trigger((new Object)->import([
                'cmd'    => 'entity',
                'strval' => 'hero',
                'intval' => $o->n,
                'argv'   => $o->e->rarity,
            ]));
        }

        if ($o->e->isEquip()) {
            $p->counterCycle->equipGain++;
            $p->counter->equipGain++;
            switch ($o->e->rarity) {
            case 4:
                $p->counterCycle->equip4starGain += $o->n;
                $p->counter->equip4starGain += $o->n;
                break;
            case 5:
                $p->counterCycle->equip5starGain += $o->n;
                $p->counter->equip5starGain += $o->n;
                break;
            }
            switch ($o->e->type) {
            case SourceEntity::TYPE_WEAPON:
                $strval = 'weapon';
                break;
            case SourceEntity::TYPE_ARMOR:
                $strval = 'armor';
                break;
            case SourceEntity::TYPE_HORSE:
                $strval = 'horse';
                break;
            case SourceEntity::TYPE_JEWEL:
                $strval = 'jewel';
                break;
            case SourceEntity::TYPE_RING:
                $strval = 'ring';
                break;
            }
            (new AchievementModule($p))->trigger((new Object)->import([
                'cmd'    => 'entity',
                'strval' => $strval,
                'intval' => $o->n,
                'argv'   => $o->e->rarity,
            ]));
        }
    }

    /**
     * @param Player $p
     * @param Global $g
     * @return void
     */
    public static function consumeGold($p, $g)
    {
        WeekMissionModule::trigger($p, $g, WeekMission::TYPE_CONSUME, $p->counterWeekly->diamondConsume);
    }

    /**
     * @param Player $p
     * @param Goods  $goods
     * @param Global $g
     * @return void
     */
    public static function recruit(Player $p, $goods, $g)
    {
        ActivityModule::diarecAspect($p, $goods, $g);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\WeekMission;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\WeekMission as PlayerWeekMission;

/**
 *
 */
class Module
{
    /**
     * @var array
     */
    /**
    protected static $handlers = [
        1 => 'commonHandler',
        2 => 'commonHandler',
        3 => 'commonHandler',
        4 => 'commonHandler',
        5 => 'commonHandler',
        6 => 'commonHandler',
        7 => 'commonHandler',
        8 => 'commonHandler',
        9 => 'commonHandler',
        10 => 'commonHandler',
        11 => 'commonHandler',
        12 => 'commonHandler',
    ];
    */

    /**
     * @var array
     */
    public static $notify = [];

    /**
     * @var integer
     */
    protected static $week = null;

    /**
     * @return void
     */
    public static $weekTypes = null;

    /**
     * @param Player $p
     * @param Object $global
     * @param integer $type
     * @param ... $argvs
     * @return void
     */
    public static function trigger(Player $p, $global, $type, ...$argvs)
    {
        self::syncWeek($global);

        if (false === isset(self::$weekTypes[$type])) {
            return;
        }

        //$handler = self::$handlers[$type];
        self::commonHandler($p, $global, $type, ...$argvs);
    }

    /**
     * @param Object $global
     * @return void
     */
    public static function syncWeek($global)
    {
        if (self::$week == $global->week) return;
        self::$week = $global->week;
        $nweek = (int)($global->epoch/604800);
        self::fetchTypes($nweek);
    }

    /**
     * @param integer $nweek
     * @return void
     */
    public static function fetchTypes($nweek)
    {
        self::$weekTypes = [];
        foreach (Store::get('weekMissionTypeIndexByFlag', 1) as $o) {
            self::$weekTypes[$o->type] = true;
        }
        
        $rotateType = Store::get('weekMissionTypeIndexByFlag', 2);
        $rotate3 = [];
        $rotate4 = [];
        foreach ($rotateType as $o) {
            if ($o->pos == 3) {
                $rotate3[] = $o;
            } else if ($o->pos == 4) {
                $rotate4[] = $o;
            }
        }
        $count3 = count($rotate3);
        $count4 = count($rotate4);
        self::$weekTypes[$rotate3[$nweek%$count3]->type] = true;
        self::$weekTypes[$rotate4[$nweek%$count4]->type] = true;
    }

    /**
     * @param Player $p
     * @param WeekMission $mission
     * @param integer $intval
     * @return void
     */
    protected static function doMission(Player $p, $mission, $intval)
    {
        $pm = new PlayerWeekMission;
        if (($found = $pm->find($p->id, self::$week, $mission->id)) && $pm->flag > PlayerWeekMission::FLAG_INIT) {
            return false;
        }

        if ($found === false) {
            $pm->pid = $p->id;
            $pm->week= self::$week;
            $pm->mid = $mission->id;
        }

        if ($intval >= $mission->intval) {
            $pm->flag = PlayerWeekMission::FLAG_DONE;
            $pm->save();
        }

        self::$notify[] = [$mission, $pm];
    }

    /**
     * @param Player $p
     * @param Object $global
     * @param integer $type
     * @param ... $argvs
     * @return void
     */
    public static function commonHandler(Player $p, $global, $type, ...$argvs)
    {
        $intval = $argvs[0];
        foreach (Store::get('weekMissionIndexByType', $type) as $mission) {
            self::doMission($p, $mission, $intval);
        }
    }
}

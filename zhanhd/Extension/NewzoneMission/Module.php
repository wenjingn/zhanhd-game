<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\NewzoneMission;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\NewzoneMission as PlayerNewzoneMission;

/**
 *
 */
class Module
{
    /**
     * @var array
     */
    protected static $handlers = [
        1 => 'depositHandler',
        2 => 'loginHandler',
        3 => 'lvlsumHandler',
        4 => 'taskHandler',
        5 => 'recruitequipHandler',
        6 => 'recruitheroHandler',
        7 => 'pvprankHandler',
        8 => 'herogainHandler',
    ];

    /**
     * @var array
     */
    public static $notify = [];

    /**
     * @param Player $p
     * @param Object $global
     * @param integer $type
     * @param ... $argvs
     * @return void 
     */
    public static function trigger(Player $p, $global, $type, ...$argvs)
    {
        if ($global->getDayFromZoneOpen() > 7) {
            return;
        }
        $handler = self::$handlers[$type];
        self::$handler($p, $global, $type, ...$argvs);
    }

    /**
     * @param Player $p
     * @param NewzoneMission $mission
     * @return void
     */
    protected static function doMission(Player $p, $mission)
    {
        $pm = new PlayerNewzoneMission;
        if (($found = $pm->find($p->id, $mission->id)) && $pm->flag > PlayerNewzoneMission::FLAG_INIT) {
            return false;
        }

        if ($found === false) {
            $pm->pid = $p->id;
            $pm->mid = $mission->id;
        }

        $pm->flag = PlayerNewzoneMission::FLAG_DONE;
        $pm->save();

        self::$notify[] = [$mission, $pm];
    }

    /**
     * @param Player $p
     * @param Object $global
     * @param integer $type
     * @param ... $argvs
     * @return void
     */
    public static function depositHandler(Player $p, $global, $type, ... $argvs)
    {
        $deposit = $argvs[0];

        foreach (Store::get('newzoneMissionIndexByType', $type) as $mission) {
            if ($mission->intval > $deposit) {
                continue;
            }

            self::doMission($p, $mission);
        }
    }

    /**
     * @param Player $p
     * @param Object $global
     * @param integer $type
     * @param ... $argvs
     * @return void
     */
    public static function loginHandler(Player $p, $global, $type, ... $argvs)
    {
        $day = $global->getDayFromZoneOpen();
        foreach (Store::get('newzoneMissionIndexByType', $type) as $mission) {
            if ($mission->getDay() > $day) {
                continue;
            }

            self::doMission($p, $mission);
        }
    }

    /**
     * @param Player $p
     * @param Object $global
     * @param integer $type
     * @param ... $argvs
     * @return void
     */
    public static function lvlsumHandler(Player $p, $global, $type, ... $argvs)
    {
        $lvlsum = $argvs[0];

        foreach (Store::get('newzoneMissionIndexByType', $type) as $mission) {
            if ($mission->intval > $lvlsum) {
                continue;
            }

            self::doMission($p, $mission);
        }
    }

    /**
     * @param Player $p
     * @param Object $global
     * @param integer $type
     * @param ... $argvs
     * @return void
     */
    public static function taskHandler(Player $p, $global, $type, ... $argvs)
    {
        $fightId = $argvs[0];
        foreach (Store::get('newzoneMissionIndexByType', $type) as $mission) {
            if ($mission->intval != $fightId) {
                continue;
            }

            self::doMission($p, $mission);
        }
    }

    /**
     * @param Player $p
     * @param Object $global
     * @param integer $type
     * @param ... $argvs
     * @return void
     */
    public static function recruitequipHandler(Player $p, $global, $type, ... $argvs)
    {
        $times = $argvs[0];
        foreach (Store::get('newzoneMissionIndexByType', $type) as $mission) {
            if ($mission->intval > $times) {
                continue;
            }

            self::doMission($p, $mission);
        }
    }

    /**
     * @param Player $p
     * @param Object $global
     * @param integer $type
     * @param ... $argvs
     * @return void
     */
    public static function recruitheroHandler(Player $p, $global, $type, ... $argvs)
    {
        $times = $argvs[0];
        foreach (Store::get('newzoneMissionIndexByType', $type) as $mission) {
            if ($mission->intval > $times) {
                continue;
            }

            self::doMission($p, $mission);
        }
    }

    /**
     * @param Player $p
     * @param Object $global
     * @param integer $type
     * @param ... $argvs
     * @return void
     */
    public static function pvprankHandler(Player $p, $global, $type, ... $argvs)
    {
        $rank = $argvs[0];
        foreach (Store::get('newzoneMissionIndexByType', $type) as $mission) {
            if ($mission->intval < $rank) {
                continue;
            }

            self::doMission($p, $mission);
        }
    }

    /**
     * @param Player $p
     * @param Object $global
     * @param integer $type
     * @param ... $argvs
     * @return void
     */
    public static function herogainHandler(Player $p, $global, $type, ... $argvs)
    {
        $star = $argvs[0];
        foreach (Store::get('newzoneMissionIndexByType', $type) as $mission) {
            if ($mission->extra) {
                if ($mission->extra != $star) {
                    continue;
                } else {
                    $key = sprintf('hero%dstarGain', $star);
                    if ($mission->intval > $p->counter->$key) {
                        continue;
                    }
                }
            } else {
                if ($mission->intval > $p->counter->heroGain) {
                    continue;
                }
            }
            self::doMission($p, $mission);
        }
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Config;

/**
 *
 */
use System\Object\ConfigObject;

/**
 *
 */
class WeekMission extends ConfigObject
{
    /**
     * @struct self
     *
     * @id     int
     * @type   int
     * @intval int
     * @rewards [(int) eid => (int) num]
     */

    /**
     * @const integer
     */
    const TYPE_DEPOSIT  = 1;
    const TYPE_LOGIN    = 2;
    const TYPE_CONSUME  = 3;
    const TYPE_HARDINS  = 4;
    const TYPE_CRAZYINS = 5;
    const TYPE_PVPWIN   = 6;
    const TYPE_LIKE     = 7;
    const TYPE_DIAREC   = 8;
    const TYPE_REFINE   = 9;
    const TYPE_FORGE    = 10;
    const TYPE_CRUSADE  = 11;
    const TYPE_TALSHOW  = 12;
}

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
class Unlock
{
    /**
     * @const integer
     */
    const PVPUNLOCK_ID = 8;

    /**
     * @var static
     */
    public static $tab = [
        1 => [1, 10104],
        2 => [1, 10201],
        3 => [1, 10206],
        4 => [1, 10206],
        5 => [1, 10206],
        6 => [1, 10301],
        7 => [1, 10304],
        self::PVPUNLOCK_ID => [1, 10405],
        9 => [1, 10505],
        10 => [1, 20101],
        11 => [1, 20205],
        12 => [1, 20406],
        13 => [2, 10505],
        14 => [1, 20606],
    ];
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Combat;

/**
 *
 */
abstract class CombatTarget
{
    /**
     *
     * @param integer $pos
     * @return array
     */
    public static function single($pos)
    {
        switch ($pos % 3) {
        case 0: return [
            2 => [2],
            5 => [5],
            1 => [1],
            4 => [4],
            0 => [0],
            3 => [3],
        ];

        case 1: return [
            1 => [1],
            4 => [4],
            2 => [2],
            5 => [5],
            0 => [0],
            3 => [3],
        ];

        case 2: return [
            0 => [0],
            3 => [3],
            1 => [1],
            4 => [4],
            2 => [2],
            5 => [5],
        ];
        }
    }

    /**
     *
     * @param integer $pos
     * @return array
     */
    public static function vertical($pos)
    {
        switch ($pos % 3) {
        case 0: return [
            2 => [2, 5], 
            5 => [5],
            1 => [1, 4], 
            4 => [4],
            0 => [0, 3],
            3 => [3],
        ];

        case 1: return [
            1 => [1, 4], 
            4 => [4], 
            2 => [2, 5], 
            5 => [5], 
            0 => [0, 3],
            3 => [3], 
        ];

        case 2: return [
            0 => [0, 3], 
            3 => [3], 
            1 => [1, 4], 
            4 => [4], 
            2 => [2, 5],
            5 => [5], 
        ];
        }
    }

    /**
     *
     * @param integer $pos
     * @return array
     */
    public static function horizontal($pos)
    {
        switch ($pos % 3) {
        case 0: return [
            2 => [2, 0, 1],
            5 => [5, 3, 4],
            1 => [1, 0],
            4 => [4, 3],
            0 => [0],
            3 => [3],
        ];

        case 1: return [
            1 => [1, 0, 2],
            4 => [4, 3, 5],
            2 => [2, 0],
            5 => [5, 3],
            0 => [0],
            3 => [3],
        ];

        case 2: return [
            0 => [0, 1, 2],
            3 => [3, 4, 5],
            1 => [1, 2],
            4 => [4, 5],
            2 => [2],
            5 => [5],
        ];
        }
    }

    /**
     *
     * @param integer $pos
     * @return array
     */
    public static function full($pos)
    {
        return [
            0 => [0, 1, 2, 3, 4, 5],
            1 => [   1, 2, 3, 4, 5],
            2 => [      2, 3, 4, 5],
            3 => [         3, 4, 5],
            4 => [            4, 5],
            5 => [               5],
        ];
    }

    /**
     *
     * @param integer $pos
     * @return array
     */
    public static function random($pos)
    {
        $ht = range(0, 5);
        shuffle($ht);

        return [$ht];
    }
}

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
class Rob
{
    /**
     * @param array $rewards
     * @param integer $attacker
     * @param integer $defender
     * @return array
     */
    public static function robResourceAddition($rewards, $attacker, $defender)
    {
        $TAB = [
            10 => 0,
            20 => 10,
            30 => 20,
            50,
        ];
		if ($attacker == 0 || $defender == 0) {
			$rate = 0;
		} else {
			$diff = abs($attacker-$defender);
			$rate = $diff*100/$defender;
		}
        foreach ($TAB as $high => $addition) {
            if ($rate < $high) {
                break;
            }
        }
        if ($attacker > $defender) {
            $addition = -$addition;
        }

        foreach ($rewards as &$n) {
            $n = (100+$addition)*$n/100;
        }

        return $rewards;
    }
}

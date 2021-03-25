<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Activity;

/**
 *
 */
use Zhanhd\Config\Activity,
    Zhanhd\Object\Player,
    Zhanhd\Object\ActivityPlan,
    Zhanhd\Object\ActivityHistory;

/**
 *
 */
class Module
{
    /**
     * @param Player $p
     * @param Goods  $goods
     * @param Global $g
     * @return void
     */
    public static function diarecAspect(Player $p, $goods, $g)
    {
        if ($goods->id != 2030 && $goods->id != 2031) return;
        $ap = new ActivityPlan;
        if (false === $ap->findByType($g->ustime/1000000, Activity::TYPE_DIAREC)) {
            return;
        }
        
        $ah = new ActivityHistory;
        if (false === $ah->find($ap->id, $p->id)) {
            $ah->aid = $ap->id;
            $ah->pid = $p->id;
        }
        
        $times = $goods->id == 2031 ? 10 : 1;
        $ah->score += $times;
        $ah->save();
    }
}

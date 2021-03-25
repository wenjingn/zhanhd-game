<?php
/**
 * $Id$
 */

/**
 *
 */
require '/data/php/games/zhanhd/shell/bootstrap.php';

/**
 *
 */
use Zhanhd\Object\Player,
    Zhanhd\Object\Player\Reward as PlayerReward,
    Zhanhd\Extension\Top32;

/**
 *
 */
define('PAGE_COUNT',   20);
define('PVP_MAX_RANK', 1000);

/**
 *
 */
$redis = $boot->globals->redis;

if (date('w') == 0) {
    $m = new Top32($boot->globals);
    $m->init();
}

/**
 *
 */
for ($i = 0; $i < PVP_MAX_RANK; $i += PAGE_COUNT) {
    if (PVP_MAX_RANK - $i < PAGE_COUNT) {
        $ceil = PVP_MAX_RANK - 1;
    } else {
        $ceil = $i + PAGE_COUNT - 1;
    }
    $ht = $redis->zrange('zhanhd:st:pvp', $i, $ceil, true);
    foreach ($ht as $pid => $rank) {
        $p = new Player;
        if (false === $p->find($pid) || $p->uid == 0) {
            continue;
        }
        $pr = new PlayerReward;
        $pr->pid = $pid;
        $pr->intval = $rank;
        $pr->from = PlayerReward::from_pvprank;
        $pr->save();
    }
}

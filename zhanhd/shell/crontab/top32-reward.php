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
use Zhanhd\Extension\Top32,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Reward as PlayerReward;

/**
 *
 */
$m = new Top32($boot->globals);
$ranks = $m->getRanks();
$set = $m->getTop32();
foreach ($ranks as $rank => $idx) {
    $rank++;
    $pid = $set[$idx];
    
    $p = new Player;
    if ($pid == 0 || false === $p->find($pid) || $p->uid == 0) {
        continue;
    }
    $pr = new PlayerReward;
    $pr->pid = $pid;
    $pr->intval = $rank;
    $pr->from = PlayerReward::from_top32;
    $pr->save();
}

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
use System\Stdlib\PhpPdo,
    System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Object\Player,
    Zhanhd\Extension\Rank\Module as RankModule;

/**
 *
 */
$argv = (new Object)->import(getlongopt([
    'id' => 0,
]));

/**
 *
 */
$last = $argv->id;
$next = true;
$rank = new RankModule;
$rank->using(RankModule::KEY_PLAYER_LEVEL);
$rank->clean();
$rank->using(RankModule::KEY_PLAYER_POWER);
$rank->clean();

/**
 *
 */
while ($next) {
    $curr = false;
    $stmt = $boot->globals->pdo->prepare(sprintf('SELECT `id` FROM `zhanhd.player`.`Player` WHERE `id` > %d ORDER BY `id` LIMIT 100', $last));
    $stmt->execute();
    foreach ($stmt->fetchall(PhpPdo::FETCH_COLUMN, 0) as $curr) {
        printf("processing player(%d) ... ", $curr);
        $p = new Player;
        if ($p->find($curr)) {
            $l = $p->getLineup(1);
            $l->lvlsum = $l->getLvlsum();
            $rank->using(RankModule::KEY_PLAYER_LEVEL);
            $rank->push($p->id, $l->lvlsum, time());

            $l->power = $l->getPower();
            $rank->using(RankModule::KEY_PLAYER_POWER);
            $rank->push($p->id, $l->power, time());
            $l->save();
            printf("done\n");
        } else {
            printf("failed\n");
            break;
        }
    }

    if ($curr) {
        $last = $curr;
    } else {
        $next = false;
    }
}

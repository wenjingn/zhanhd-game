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
use Zhanhd\Object\Guild,
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
$rank->using(RankModule::KEY_GUILD_RANK);
$rank->clean();

/**
 *
 */
while ($next) {
    $curr = false;
    $stmt = $boot->globals->pdo->prepare(sprintf('SELECT `id` FROM `zhanhd.player`.`Guild` WHERE `id` > %d ORDER BY `id` LIMIT 100', $last));
    $stmt->execute();
    foreach ($stmt->fetchall(PhpPdo::FETCH_COLUMN, 0) as $curr) {
        printf("processing guild(%d) ... ", $curr);
        $g = new Guild;
        if ($g->find($curr)) {
            $rank->pushGuild($g->id, $g->getRankScore());
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

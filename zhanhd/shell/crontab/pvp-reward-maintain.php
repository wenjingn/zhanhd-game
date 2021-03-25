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
use Zhanhd\Object\Player;

/**
 *
 */
define('PAGE_COUNT', 20);
define('REDIS_KEY', 'zhanhd:st:pvp');

/**
 *
 */
$redis = $boot->globals->redis;

/**
 *
 */
$removed = 0;
$i = 0;
$p = new Player;
for ($i = 0; $ht = $redis->zrange(REDIS_KEY, $i, $i + PAGE_COUNT - 1, true); $i += PAGE_COUNT) {
    foreach ($ht as $pid => $rank) {
        if (false === $p->find($pid)) {
            $redis->zrem(REDIS_KEY, $pid);
            $removed++;
            $i--;
        } else if ($removed) {
            $redis->zincrby(REDIS_KEY, -$removed, $pid);
        }
    }
}

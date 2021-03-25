<?php
/**
 * $Id$
 */

/**
 *
 */
require '/data/php/games/zhanhd/shell/bootstrap.php';
require '/data/php/games/zhanhd/performance/runtime.php';

/**
 *
 */
use Zhanhd\Extension\Identity;

/**
 *
 */
$pdo = $boot->globals->pdo;
$rds = $boot->globals->redis;
$r = new Runtime;
for ($i = 0; $i < 94; $i++) {
    $id = Identity::generatePId($rds);
    printf("%d\n", $id);
}
unset($r);

$r = new Runtime;
for ($i = 0; $i < 94; $i++) {
    $id = Identity::generatePeId($rds);
    printf("%d\n", $id);
}
unset($r);

$r = new Runtime;
for ($i = 0; $i < 94; $i++) {
    $id = Identity::generate();
    printf("%d\n", $id);
}

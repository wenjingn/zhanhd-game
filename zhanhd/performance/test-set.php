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
use System\ReqRes\Set,
    System\ReqRes\Int\U08;

/**
 *
 */
$set = new Set(new U08);
$set->resize(4);
foreach ($set as $i => $o) {
    $o->intval($i);
}

$set->sub(3, 2);
print_r($set);

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
$pdo = $boot->globals->pdo;
$stmt = $pdo->query('SELECT * FROM `zhanhd.config`.`Error`');

while ($o = $stmt->fetchObject()) {
    printf("INSERT INTO error VALUES (%d, '%s');\n", $o->code, $o->info);
}

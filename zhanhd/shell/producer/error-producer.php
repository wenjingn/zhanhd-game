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

/**
 *
 */
$stmt = $pdo->query('SELECT * FROM `zhanhd.config`.`Error`');
$errors = [];
while ($o = $stmt->fetch(PDO::FETCH_OBJ)) {
    $errors[$o->error] = $o;
}

/**
 *
 */
$exceptions = `grep -rnH '\->errorResponse->error' /data/php/games/zhanhd --exclude-dir=.svn`;
$exceptions = explode("\n", $exceptions);

foreach ($exceptions as $exception) {
    if (preg_match('/error\([\'"]([\s\S]+)[\'"]\)/', $exception, $matches)) {
        $error = $matches[1];
        
        if (false === isset($errors[$error])) {
            printf("insert into `zhanhd.config`.`Error` VALUES (NULL, '%s', '');\n", $error);
        }

        $errors[$error] = true;
    }
}

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
use Zhanhd\Object\Player\Rob\Log;

/**
 *
 */
function testGetsByPid($g)
{
    $logs = Log::getsByPid($g->pdo, 1, $g->ustime);
    print_r($logs);
}

testGetsByPid($boot->globals);

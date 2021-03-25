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
var_dump(memory_get_usage());
$p = new Player;
$p->find(3);
var_dump(memory_get_usage());

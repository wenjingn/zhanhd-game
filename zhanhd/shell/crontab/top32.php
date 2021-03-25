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
use Zhanhd\Extension\Top32;

/**
 *
 */
$m = new Top32($boot->globals);
$m->run();

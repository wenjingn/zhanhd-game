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
use Zhanhd\Config\Store;

/**
 *
 */

use Zhanhd\Extension\PointsRace;

$m = new PointsRace($boot->globals);
var_dump($m->cycle);
var_dump($m->ctime);

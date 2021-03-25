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
use Zhanhd\Extension\PointsRace;

/**
 *
 */
function testGenlist($g)
{
    $m = new PointsRace($g);
    $list = $m->genlist(1);
    print_r($list);
}

testGenlist($boot->globals);

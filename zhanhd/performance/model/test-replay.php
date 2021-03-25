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
use Zhanhd\Object\Replay,
    Zhanhd\ReqRes\CombatProcessInfo;

/**
 *
 */
function test()
{
    $replay = new Replay;
    $replay->find(4);
    $combat = new CombatProcessInfo;
    $combat->decode($replay->combat);
    print_r($combat);
}

test();

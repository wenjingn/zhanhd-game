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
function testRandomIds($g)
{
    $ids = Player::randomIds($g->pdo, 9, ['id!=2']);
    print_r($ids);
}

function testRobRewards()
{
    $p = new Player;
    $p->find(1);
    $rewards = $p->robResource();
    print_r($rewards);
}

function testGetLineup()
{
    $p = new Player;
    $p->find(1);
    $pl = $p->getLineup(1);
    print_r($pl);
}

testRandomIds($boot->globals);
testRobRewards();
testGetLineup();

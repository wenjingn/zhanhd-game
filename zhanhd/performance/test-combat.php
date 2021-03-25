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
use Zhanhd\Object\Player\Lineup as PlayerLineup,
    Zhanhd\Config\Store,
    Zhanhd\Extension\Combat\Module,
    Zhanhd\ReqRes\CombatProcessInfo;

/**
 *
 */
function testPvN()
{
    $l1 = new PlayerLineup;
    $l1->find(1, 1);
    $info = new CombatProcessInfo;
    $m = new Module;
    $m->combat(null, $l1, $info);

    print_r($info);
}

function testPvp()
{
    $l1 = new PlayerLineup;
    $l1->find(1,1);

    $l2 = new PlayerLineup;
    $l2->find(16,1);
    
    $info = new CombatProcessInfo;
    $m = new Module;
    $m->combat($l1, $l2, $info);
    print_r($info);
}

function testPve()
{
    $l1 = new PlayerLineup;
    $l1->find(1,1);
    $ins = Store::get('ins2', 10202);
    $evt = $ins->getEvent(1);
    $l2 = $evt->getNpcLineup();

    $info = new CombatProcessInfo;
    $m = new Module;
    $m->combat($l1, $l2, $info);
    print_r($info);
}

testPve();

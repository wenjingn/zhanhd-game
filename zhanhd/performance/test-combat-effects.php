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
use System\Stdlib\PhpPdo,
    System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Object\Player;
use Zhanhd\Extension\Combat\Module as CombatModule;
use Zhanhd\ReqRes\PvpRank\Attack\Response;

/**
 *
 */
$p1 = new Player;
$p1->find(3);
$p2 = new Player;
$p2->find(16);

$r = new Response;
(new CombatModule)->combat($p1->getLineups('gid')->get(1), $p2->getLineups('gid')->get(1), $r->combat);
print_r($r->combat);

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
use Zhanhd\Object\Player\Lineup;

/**
 *
 */
function testGetBonds()
{
	$pl = new Lineup;
	$pl->find(1, 1);

	$bonds = $pl->getBonds();
	print_r($bonds);
}

testGetBonds();

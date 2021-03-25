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
use Zhanhd\Object\Player\Coherence\Daily as CoherenceDaily;

/**
 *
 */
var_dump(CoherenceDaily::increase($boot->globals->pdo, 1191, 20170327, 'robbed', 1, 20));

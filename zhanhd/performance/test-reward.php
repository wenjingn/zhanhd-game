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
print_r(Store::get('reward', 4)->getRankCoherenceRewards(1));

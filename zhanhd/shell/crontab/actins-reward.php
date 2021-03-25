<?php
/**
 * $Id$
 */

/**
 *
 */
if (date('w') != 1) {
    exit;
}

/**
 *
 */
require '/data/php/games/zhanhd/shell/bootstrap.php';

/**
 *
 */
use Zhanhd\Extension\ActIns\Module as ActInsModule,
    Zhanhd\Extension\Mail\Module   as MailModule;

/**
 *
 */
$redis = $boot->globals->redis;
$lastWeek = $boot->globals->week-1;
$ranklist = ActInsModule::rankList($redis, $lastWeek);

$rank = 1;
foreach ($ranklist as $k => $v) {
    MailModule::actinsRewardAspect($k, $rank++);
}

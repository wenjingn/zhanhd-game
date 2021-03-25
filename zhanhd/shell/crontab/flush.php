<?php
/**
 * $Id$
 */

/**
 * @commet
 *
 * @excuted by crontab
 * @4:00 everyday
 */
require '/data/php/games/zhanhd/shell/bootstrap.php';

/**
 *
 */
use System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Extension\FriendShipStore\Module as FriendShipStoreModule,
    Zhanhd\Extension\Question\Module        as QuestionModule,
    Zhanhd\Extension\ActIns\Module          as ActInsModule,
    Zhanhd\Extension\WorldBoss\Module       as WorldBossModule;

/**
 *
 */
$argvs = (new Object)->import(getlongopt([
    'next' => 1,
]));

/**
 *
 */
$g = $boot->globals;
$redis = $g->redis;

/* run daily */
if ($argvs->next) {
    $now = ustime();
    $g->setTime($now+86400*1000000); // setTime tomorrow
    FriendShipStoreModule::push($redis, $g->date, FriendShipStoreModule::generate());
    QuestionModule::push($redis, $g->date, QuestionModule::generate());
    (new WorldBossModule($g))->generate();
} else {
    FriendShipStoreModule::push($redis, $g->date, FriendShipStoreModule::generate());
    QuestionModule::push($redis, $g->date, QuestionModule::generate());
    (new WorldBossModule($g))->generate();
}

/* run weekly on sunday */
if (date('w') == 0) {
    if ($argvs->next) {
        $g->setTime($now+86400*1000000*7); // setTime next week
        ActInsModule::push($redis, $g->week, ActInsModule::generate($g));
    } else {
        ActInsModule::push($redis, $g->week, ActInsModule::generate($g));
    }
}

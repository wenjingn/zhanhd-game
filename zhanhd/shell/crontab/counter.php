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
$pdo = $boot->globals->pdo;
$now = $boot->globals->ustime;

/**
 *
 */
$yestoday = date('Ymd', ($now - 86400*1000000)/1000000);

$counterKey = [
    'loginDays',
    'onlineDur',
    'diamondDeposit',
    'diamondGain',
    'goods-2030',
    'goods-2031',
    'resourceRecruit',
    'heroGain',
    'hero4starGain',
    'hero5starGain',
    'equipGain',
    'equip4starGain',
    'equip5starGain',
    'goods-204',
    'propconsume410106',
    'goods-214',
    'goods-218',
    'goods-216',
    'goods-217',
    'resourceCollect',
    'equipRecruit',
    'talent',
    'instanceNormal',
    'instanceHard',
    'instanceCrazy',
    'crusade',
    'actins',
    'pvp',
];

foreach ($counterKey as $key) {
    $stmt = $pdo->prepare('select sum(v) from `zhanhd.player`.`PlayerCounterCycle` where `cycle`=? and `k`=?');
    $stmt->execute([$yestoday, $key]);
    $count = (integer)$stmt->fetchColumn(0);

    $stmt = $pdo->prepare('insert into `zhanhd.player`.`CounterDaily` values (?, ?, ?)');
    $stmt->execute([$yestoday, $key, $count]);
}

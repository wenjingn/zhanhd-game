<?php
/**
 * $Id$
 */

/**
 * @comment
 * 
 * @execute by crontab
 * @12:00:00 every day
 */

/**
 *
 */
require '/data/php/games/zhanhd/shell/bootstrap.php';

/**
 *
 */
use Zhanhd\Object\ActivityPlan,
    Zhanhd\Object\ActivityHistory,
    Zhanhd\Object\Player\Reward as PlayerReward,
    Zhanhd\Extension\Mail\Module as MailModule;

/**
 *
 */
define('PAGE_COUNT', 20);

/**
 *
 */
$pdo    = $boot->globals->pdo;
$redis  = $boot->globals->redis;
$ustime = $boot->globals->ustime;
$expire = $ustime + 86400 * 7 * 1000000;

/**
 *
 */
$activities = ActivityPlan::ends($pdo, $ustime);
foreach ($activities as $o) {
    $key = $o->redisKey();
    $rank = 1;
    for ($i = 0; $rankList = $redis->zrevrange($key, $i, $i + PAGE_COUNT - 1, true); $i += PAGE_COUNT) {
        foreach ($rankList as $pid => $encoded) {
            $decoded = $o->decode($encoded);
            $score = $decoded['score'];

            MailModule::activityRankRewardAspect($pid, $o->aid, $rank);
            MailModule::activityScoreRewardAspect($pid, $o->aid, $score);

            $ah = new ActivityHistory;
            $ah->aid   = $o->id;
            $ah->pid   = $pid;
            $ah->rank  = $rank;
            $ah->score = $score;
            $ah->save();

            $rank ++;
        }
    }

    $o->status = ActivityPlan::STATUS_SETTLED;
    $o->save();
}

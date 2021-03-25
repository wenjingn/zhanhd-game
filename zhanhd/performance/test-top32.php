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
use Zhanhd\Extension\Top32;

/**
 *
 */
$m = new Top32($boot->globals);

/**
 *
 */
function test1(){
    for ($i = 0; $i < 32; $i++) {
        $info = $m->getCompetitionDetail($i);
        printf("%d:%d -> %d\n", $info['attacker'], $info['defender'], $info['win']);
    }

    for ($i = 0; $i < 31; $i++) {
        printf("$i:%d\n", $m->getTop32ByIndex($i));
    }

    $retval = $m->getRetval();
    for ($i = 0; $i < 32; $i++) {
        printf("%d", (boolean)(1<<(31-$i)&$retval));
        if ($i == 15) {
            printf(" ");
        } else if ($i == 23) {
            printf(" ");
        } else if ($i == 27) {
            printf(" ");
        } else if ($i == 29) {
            printf(" ");
        }
    }
    printf("\n");

    $m->getRanks();
}

function testGetCompetitions($g)
{
    $m = new Top32($g);
    $competitions = new Zhanhd\ReqRes\Top32\Poll\Response;
    $m->getCompetitionInfo($competitions->status);
    print_r($competitions);
}

function testCompetitionFinishedCount($g)
{
    $m = new Top32($g);
    var_dump($m->competitionFinishedCount());
}

function testInit($g)
{
	$g->setTime(strtotime('2017-04-23 22:00:00')*1000000);
    $m = new Top32($g);
    $m->init();
}

function testClean($g)
{
    $m = new Top32($g);
    $m->clean();
}

function testGlobalMsg($g)
{
	$m = new Top32($g);
	$m->globalMsg($g);
}

$g = $boot->globals;
testGlobalMsg($g);

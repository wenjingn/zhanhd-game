<?php
/**
 * $Id$
 */

/**
 *
 */
include '/data/php/games/zhanhd/tests/Robot.php';

/**
 *
 */
use System\Stdlib\Object,
    System\Swoole\ReqResHeader;

/**
 *
 */
use Zhanhd\ReqRes\Account\Signup\Response as SignupResponse,
    Zhanhd\ReqRes\Account\InitResponse,
    Zhanhd\ReqRes\NewzoneMission\UpdateResponse,
    Zhanhd\ReqRes\WeekMission\UpdateResponse as WeekMissionUpdateResponse;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,
)));

/**
 *
 */
$robot = new Robot($config->login);

/**
 *
 */
$robot->on(3, function($c, $qargv) {
    $res = new SignupResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on login success
 */
$robot->on(9, function($c, $qargv) {
    $c->send(Robot::encodeWithHeader(9999));
});

/* on newzone mission update response */
$robot->on(156, function($c, $qargv) {
    $res = new UpdateResponse;
    $res->decode($qargv);
    print_r($res);
});

/* on week mission update response */
$robot->on(161, function($c, $qargv){
    $res = new WeekMissionUpdateResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

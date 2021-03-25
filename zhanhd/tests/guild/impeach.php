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
    Zhanhd\ReqRes\Guild\Impeach\Response as GuildImpeachResponse,
    Zhanhd\ReqRes\Guild\Impeach\NewPresidentNotify;

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
    $c->send(Robot::encodeWithHeader(211));
});

/* on guild impeach response */
$robot->on(212, function($c, $qargv) {
    $res = new GuildImpeachResponse;
    $res->decode($qargv);
    print_r($res);
});

/* on guild impeach success response */
$robot->on(220, function($c, $qargv) {
    $res = new NewPresidentNotify;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

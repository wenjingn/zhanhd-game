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
use Zhanhd\ReqRes\Account\Signup\Response    as SignupResponse,
    Zhanhd\ReqRes\Account\InitResponse,
    Zhanhd\ReqRes\Account\AutoSignin\Request as AutoSigninRequest;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,

    'secret' => false,
    'zone'   => false,
)));

/**
 *
 */
$robot = new Robot(false);

/**
 *
 */
$robot->on('connect', function($c, $qargv) use ($config) {
    $req = new AutoSigninRequest;
    $req->secret->strval($config->secret);
    $req->zone->intval($config->zone);
    $c->send(Robot::encodeWithHeader(5, $req));
});

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
    $res = new InitResponse;
    $res->decode($qargv);
    print_r($res);
    var_dump($res->user->secret->strval());
});

/**
 *
 */
$robot->connect($config->host, $config->port);

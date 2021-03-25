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
    Zhanhd\ReqRes\Guild\Apply\Request,
    Zhanhd\ReqRes\Guild\Pending\Approve\Notify,
    Zhanhd\ReqRes\Guild\Member\Quit\Response;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'gid' => false,
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
$robot->on(9, function($c, $qargv) use ($config) {
    $req = new Request;
    $req->gid->intval($config->gid);
    $c->send(Robot::encodeWithHeader(194, $req));
});

/* on approve notify */
$robot->on(195, function($c, $qargv) {
    $res = new Notify;
    $res->decode($qargv);
    print_r($res);

    printf("是否退会?y/n");
    $line = trim(fgets(STDIN));
    if ($line == 'y') {
        $c->send(Robot::encodeWithHeader(207));
    }
});

/* on quit response */
$robot->on(208, function($c, $qargv) {
    $res = new Response;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

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
    Zhanhd\ReqRes\ActIns\Request,
    Zhanhd\ReqRes\ActIns\Response,
    Zhanhd\ReqRes\ActIns\UpdateResponse;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'aid' => false,
    'floor' => false,
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
    $req->aid->intval($config->aid);
    $req->floor->intval($config->floor);
    $req->gid->intval($config->gid);
    $c->send(Robot::encodeWithHeader(145, $req));
});

/**
 * on actins response
 */
$robot->on(146, function($c, $qargv) {
    $res = new Response;
    $res->decode($qargv);
    print_r($res);
});

/* on actins-update response */
$robot->on(147, function($c, $qargv) {
    $res = new UpdateResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

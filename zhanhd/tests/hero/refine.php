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
    Zhanhd\ReqRes\Hero\Refine\Request,
    Zhanhd\ReqRes\Hero\Refine\Response,
    Zhanhd\ReqRes\PropUse\PropRemainResponse;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'peid' => false,
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
    $req->peid->intval($config->peid);
    $c->send(Robot::encodeWithHeader(150, $req));
});

/* on refine response */
$robot->on(151, function($c, $qargv) {
    $res = new Response;
    $res->decode($qargv);
    print_r($res);
});

/* on propremain response */
$robot->on(108, function($c, $qargv) {
    $res = new PropRemainResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

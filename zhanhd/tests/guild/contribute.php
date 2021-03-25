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
    Zhanhd\ReqRes\Guild\Contribute\Request,
    Zhanhd\ReqRes\Guild\Contribute\Response,
    Zhanhd\ReqRes\Guild\Contribute\Notify,
    Zhanhd\ReqRes\Guild\ExpNotify;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'cid' => false,
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
    $req->cid->intval($config->cid);
    $c->send(Robot::encodeWithHeader(213, $req));
});

/* on guild contribute response */
$robot->on(214, function($c, $qargv) {
    $res = new Response;
    $res->decode($qargv);
    print_r($res);
});

/* on guild expnotify */
$robot->on(234, function($c, $qargv) {
    $res = new ExpNotify;
    $res->decode($qargv);
    print_r($res);
});

/* on guild contribute response */
$robot->on(272, function($c, $qargv) {
    $res = new Notify;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

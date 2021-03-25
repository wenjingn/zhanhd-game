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
    Zhanhd\ReqRes\Crusade\Reward\Request,
    Zhanhd\ReqRes\Crusade\Reward\Response,
    Zhanhd\ReqRes\HeroUpgradeResponse,
    Zhanhd\ReqRes\Building\ResourceResponse;

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
    $c->send(Robot::encodeWithHeader(38, $req));
});

/**
 * on resource response
 */
$robot->on(11, function($c, $qargv) {
    $res = new ResourceResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on hero upgrade response
 */
$robot->on(40, function($c, $qargv) {
    $res = new HeroUpgradeResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on crusade reward response
 */
$robot->on(39, function($c, $qargv) {
    $res = new Response;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

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
    Zhanhd\ReqRes\WorldBoss\Gateway\Request,
    Zhanhd\ReqRes\WorldBoss\Gateway\Response,
    Zhanhd\ReqRes\WorldBoss\Notify\MetaResponse,
    Zhanhd\ReqRes\WorldBoss\Notify\RankResponse;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'flag' => false,
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
    $req->flag->intval($config->flag);
    $c->send(Robot::encodeWithHeader(174, $req));
});

/**
 *
 */
$robot->on(175, function($c, $qargv) {
    $r = new Response;
    $r->decode($qargv);
    print_r($r);
});

/**
 *
 */
$robot->on(176, function($c, $qargv){
    $res = new MetaResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->on(177, function($c, $qargv){
    $res = new RankResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

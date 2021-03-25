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
    Zhanhd\ReqRes\Relation\Love\Request,
    Zhanhd\ReqRes\Relation\Love\Response,
    Zhanhd\ReqRes\Building\ResourceResponse;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'flag'  => false,
    'fid'   => false,
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
    $req->fid ->intval($config->fid);
    $c->send(Robot::encodeWithHeader(68, $req));
});

/**
 * on relation love response
 */
$robot->on(69, function($c, $qargv) {
    $res = new Response;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on building resource response
 */
$robot->on(11, function($c, $qargv) {
    $res = new ResourceResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

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
    Zhanhd\ReqRes\Store\FriendShip\Request,
    Zhanhd\ReqRes\Store\FriendShip\Response,
    Zhanhd\ReqRes\Store\FriendShipStore,
    Zhanhd\ReqRes\Relation\FriendShipUpdateResponse;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'version' => false,
    'gid'     => false,
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
    $req->version->intval($config->version);
    $req->gid->intval($config->gid);

    $c->send(Robot::encodeWithHeader(141, $req));
});

/* on buy res */
$robot->on(142, function($c, $qargv) {
    $res = new Response;
    $res->decode($qargv);
    print_r($res);
});

/* on wrong version res */
$robot->on(140, function($c, $qargv) {
    $res = new FriendShipStore;
    $res->decode($qargv);
    print_r($res);
});

/* friendship update response */
$robot->on(127, function($c, $qargv){
    $res = new FriendShipUpdateResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

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
    Zhanhd\ReqRes\Achievement\AcceptRequest,
    Zhanhd\ReqRes\Achievement\AcceptResponse,
    Zhanhd\ReqRes\Achievement\UpdateResponse,
    Zhanhd\ReqRes\Building\ResourceResponse;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,
    
    'aid' => false,
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
    $req = new AcceptRequest;
    $req->aid->intval($config->aid);
    $c->send(Robot::encodeWithHeader(75, $req));
});

/**
 * on accepte response
 */
$robot->on(76, function($c, $qargv) {
    $res = new AcceptResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on update response
 */
$robot->on(74, function($c, $qargv) {
    $res = new UpdateResponse;
    $res->decode($qargv);
    print_r($res);
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
 *
 */
$robot->connect($config->host, $config->port);

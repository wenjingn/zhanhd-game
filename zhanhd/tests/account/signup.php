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
use Zhanhd\ReqRes\Account\Signup\Request  as SignupRequest,
    Zhanhd\ReqRes\Account\Signup\Response as SignupResponse;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 0,

    'username' => false,
    'password' => false,
    'email'    => null,
    'zone'     => false,
)));

/**
 *
 */
$robot = new Robot($config->login);

/**
 *
 */
$robot->on('connect', function($c, $qargv) use ($config) {
    $req = new SignupRequest;
    $req->login->strval($config->username);
    $req->passwd->strval($config->password);
    $req->email->strval($config->email);
    $req->zone->intval($config->zone);
    $c->send(Robot::encodeWithHeader(2, $req));
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
 *
 */
$robot->connect($config->host, $config->port);

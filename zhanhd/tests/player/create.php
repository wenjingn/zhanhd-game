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
use Zhanhd\ReqRes\Player\Create\Request   as CreateRequest,
    Zhanhd\ReqRes\Player\Create\Response  as CreateResponse,
    Zhanhd\ReqRes\Account\Signup\Response as SignupResponse,
    Zhanhd\ReqRes\Account\InitResponse;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'nickname' => false,
)));

/**
 *
 */
$robot = new Robot($config->login);

/**
 *
 */
$robot->on(3, function($c, $qargv) use ($config) {
    $res = new SignupResponse;
    $res->decode($qargv);
    print_r($res);
    
    $req = new CreateRequest;
    $req->nick->strval($config->nickname);
    $c->send(Robot::encodeWithHeader(106, $req));
});

/**
 * on login success
 */
$robot->on(9, function($c, $qargv) use ($config) {
    $res = new InitResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

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
    Zhanhd\ReqRes\Guild\Search\Request,
    Zhanhd\ReqRes\Guild\Search\Response;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'keyword' => '',
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
    $req->keyword->strval($config->keyword);
    $c->send(Robot::encodeWithHeader(192, $req));
});

/* on search response */
$robot->on(193, function($c, $qargv) use ($config) {
    $res = new Response;        
    $res->decode($qargv);
    print_r($res);
    file_put_contents('/tmp/res', $res->encode());
});

/**
 *
 */
$robot->connect($config->host, $config->port);

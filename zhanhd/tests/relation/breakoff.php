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
    Zhanhd\ReqRes\Relation\BreakOff\Request  as RelationBreakOffRequest,
    Zhanhd\ReqRes\Relation\BreakOff\Response as RelationBreakOffResponse;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'friends' => false,
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
    $friends = explode(',', $config->friends);
    $req = new RelationBreakOffRequest;
    $req->friends->resize(count($friends));
    foreach ($req->friends as $i => $o) {
        $o->intval($friends[$i]);
    }
    $c->send(Robot::encodeWithHeader(123, $req));
});

/**
 * on relation break off response
 */
$robot->on(124, function($c, $qargv) {
    $res = new RelationBreakOffResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

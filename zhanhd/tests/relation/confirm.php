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
    Zhanhd\ReqRes\Relation\Confirm\Request as RelationConfirmRequest,
    Zhanhd\ReqRes\Relation\Communicate\Response as RelationCommunicateResponse,
    Zhanhd\ReqRes\Relation\Refuse\Response as RelationRefuseResponse;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'friends' => false,
    'flag'    => false,
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

    $req = new RelationConfirmRequest;
    $req->flag->intval($config->flag);
    $req->friends->resize(count($friends));
    foreach ($req->friends as $i => $o) {
        $o->intval($friends[$i]);
    }

    $c->send(Robot::encodeWithHeader(64, $req));
});

/**
 * on confirm friend
 */
$robot->on(65, function($c, $qargv) {
    $res = new RelationCommunicateResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on relation refuse response
 */
$robot->on(121, function($c, $qargv) {
    $res = new RelationRefuseResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

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
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\ReqRes\RewardMail\Receive\Request,
    Zhanhd\ReqRes\RewardMail\Receive\Response,
    Zhanhd\ReqRes\Relation\FriendShipUpdateResponse;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'rewardIds' => false,
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
    $rewardIds = explode(',', $config->rewardIds);
    $req = new Request;
    $req->rewardIds->resize(count($rewardIds));
    foreach ($req->rewardIds as $i => $o) {
        $o->intval($rewardIds[$i]);
    }
    $c->send(Robot::encodeWithHeader(53, $req));
});

/**
 * on reward response
 */
$robot->on(54, function($c, $qargv) {
    $res = new Response;
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

/* on medal response */
$robot->on(127, function($c, $qargv){
    $res = new FriendShipUpdateResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

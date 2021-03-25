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
    Zhanhd\ReqRes\Store\Prop\Request,
    Zhanhd\ReqRes\Store\Prop\Response,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\ReqRes\PropUse\HeroPackageCapacityResponse,
    Zhanhd\ReqRes\PropUse\PackageCapacityResponse,
    Zhanhd\ReqRes\WeekMission\UpdateResponse as WeekMissionUpdateResponse;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,
    'gid' => false,
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
    $req->gid->intval($config->gid);
    $c->send(Robot::encodeWithHeader(77, $req));
});

/* on store prop response */
$robot->on(78, function($c, $qargv) {
    $res = new Response;
    $res->decode($qargv);
    print_r($res);
});

/* on resource response */
$robot->on(11, function($c, $qargv) {
    $res = new ResourceResponse;
    $res->decode($qargv);
    print_r($res);
});

/* on package capacity response */
$robot->on(87, function($c, $qargv) {
    $res = new PackageCapacityResponse;
    $res->decode($qargv);
    print_r($res);
});

/* on hero package capacity response */
$robot->on(88, function($c, $qargv) {
    $res = new HeroPackageCapactiyResponse;
    $res->decode($qargv);
    print_r($res);
});

/* on week mission response */
$robot->on(161, function($c, $qargv){
    $res = new WeekMissionUpdateResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

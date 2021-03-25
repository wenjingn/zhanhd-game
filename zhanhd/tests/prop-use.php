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
    Zhanhd\ReqRes\PropUse\Request,
    Zhanhd\ReqRes\PropUse\Response,
    Zhanhd\ReqRes\PropUse\PackageCapacityResponse,
    Zhanhd\ReqRes\PropUse\HeroPackageCapacityResponse,
    Zhanhd\ReqRes\PropUse\HeroEnergyResponse,
    Zhanhd\ReqRes\PropUse\PropRemainResponse,
    Zhanhd\ReqRes\Building\ResourceResponse;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'propId' => false,
    'num'    => false,
    'gid'    => false,
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
    $req->propId->intval($config->propId);
    $req->num->intval($config->num);
    $req->gid->intval($config->gid);
    $c->send(Robot::encodeWithHeader(79, $req));
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
 * on package capacity response
 */
$robot->on(87, function($c, $qargv) {
    $res = new PackageCapacityResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on hero package capacity response
 */
$robot->on(88, function($c, $qargv) {
    $res = new HeroPackageCapacityResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on hero energy response
 */
$robot->on(86, function($c, $qargv) {
    $res = new HeroEnergyResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on propuse response
 */
$robot->on(80, function($c, $qargv) {
    $res = new Response;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on prop remain response
 */
$robot->on(108, function($c, $qargv) {
    $res = new PropRemainResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

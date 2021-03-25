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
    Zhanhd\ReqRes\RecruitRequest,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\ReqRes\Recruit\HeroResponse,
    Zhanhd\ReqRes\Recruit\PropResponse,
    Zhanhd\ReqRes\Recruit\EquipResponse,
    Zhanhd\ReqRes\PropUse\PropRemainResponse,
    Zhanhd\ReqRes\PropUse\HeroPackageCapacityResponse,
    Zhanhd\ReqRes\PropUse\PackageCapacityResponse;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'cmd' => false,
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
    $req = new RecruitRequest;
    $req->gid->intval($config->gid);
    $c->send(Robot::encodeWithHeader($config->cmd, $req));
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
 * on recruit hero response
 */
$robot->on(13, function($c, $qargv) {
    $res = new HeroResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on recruit equip response
 */
$robot->on(28, function($c, $qargv) {
    $res = new EquipResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on recruit prop response
 */
$robot->on(78, function($c, $qargv) {
    $res = new PropResponse;
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
 * on package capacity response
 */
$robot->on(87, function($c, $qargv){
    $res = new PackageCapacityResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on  hero package capacity response
 */
$robot->on(88, function($c, $qargv){
    $res = new HeroPackageCapacityResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

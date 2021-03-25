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
    Zhanhd\ReqRes\LeaderInfo,
    Zhanhd\ReqRes\Player\LeaderResponse;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'nickname' => false,
    'sex'      => false,

    'hair' => false,
    'face' => false,
    'clothes' => false,
    'hairclr' => false,
    'faceclr' => false,
    'eyeclr'  => false,
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
    $req = new LeaderInfo;
    $req->name->strval($config->nickname);
    $req->sex->intval($config->sex);

    $req->img->hair->intval($config->hair);
    $req->img->face->intval($config->face);
    $req->img->clothes->intval($config->clothes);
    $req->img->hairclr->intval($config->hairclr);
    $req->img->faceclr->intval($config->faceclr);
    $req->img->eyeclr->intval($config->eyeclr);

    $c->send(Robot::encodeWithHeader(47, $req));
});

/**
 * on leader info response
 */
$robot->on(48, function($c, $qargv) {
    $res = new LeaderResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

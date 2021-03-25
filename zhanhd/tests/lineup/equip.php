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
    Zhanhd\ReqRes\Lineup\Equip            as Request,
    Zhanhd\ReqRes\Lineup\EquipResponse    as Response;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'gid'   => false,
    'pos'   => false,
    'use'   => false,
    'equip' => false,
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
    $req->pos->intval($config->pos);
    $req->use->intval($config->use);
    $req->pe->peid->intval($config->equip);
    $c->send(Robot::encodeWithHeader(25, $req));
});

/* equip response */
$robot->on(26, function($c, $qargv) {
    $res = new Response;        
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

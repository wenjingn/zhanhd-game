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
    Zhanhd\ReqRes\Hero\Upgrade\Request,
    Zhanhd\ReqRes\Hero\Upgrade\Response;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'peid' => false,
    'expcardNum'   => false,
    'eliminatings' => false,
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
    $req->peid->intval($config->peid);

    $req->expcardNum->intval($config->expcardNum);
    $eliminatings = explode(',', $config->eliminatings);
    $req->eliminatings->resize(count($eliminatings));
    foreach ($req->eliminatings as $i => $o) {
        $o->intval($eliminatings[$i]);
    }

    $c->send(Robot::encodeWithHeader(113, $req));
});

/**
 * on hero upgrade response
 */
$robot->on(114, function($c, $qargv) {
    $res = new Response;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

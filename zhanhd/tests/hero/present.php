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
    Zhanhd\ReqRes\Hero\Present\Request,
    Zhanhd\ReqRes\Hero\Present\Response;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,
    'peid' => false,
    'eids'  => false,
    'nums'  => false,
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
    $eids = explode(',', $config->eids);
    $nums = explode(',', $config->nums);
    $req->props->resize(count($eids));
    foreach ($req->props as $i => $o) {
        $o->eid->intval($eids[$i]);
        $o->num->intval($nums[$i]);
    }
    $c->send(Robot::encodeWithHeader(68, $req));
});

/**
 *
 */
$robot->on(69, function($c, $qargv) {
    $res = new Response;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

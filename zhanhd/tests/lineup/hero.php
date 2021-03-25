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
    Zhanhd\ReqRes\Lineup\Hero\Request,
    Zhanhd\ReqRes\Lineup\Hero\Response;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'fid' => false,
    'gid' => false,
    'peids' => false,
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
    $req->fid->intval($config->fid);
    $peids = explode(',', $config->peids);
    $req->lineups->resize(count($peids));
    foreach ($req->lineups as $i => $o) {
        $o->pos->intval($i);
        $o->pe->peid->intval($peids[$i]);
    }
    $c->send(Robot::encodeWithHeader(14, $req));
});

/* on lineup response */
$robot->on(15, function($c, $qargv) {
    $res = new Response;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

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
    Zhanhd\ReqRes\HeartBeatResponse,
    Zhanhd\ReqRes\MessageMail\NotifyResponse as MessageMailResponse,
    Zhanhd\ReqRes\RewardMail\NotifyResponse as RewardMailResponse,
    Zhanhd\ReqRes\DepositResponse,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\ReqRes\Relation\Confirm\Response as RelationConfirmResponse,
    Zhanhd\ReqRes\Relation\Communicate\Response as RelationCommunicateResponse,
    Zhanhd\ReqRes\Relation\Refuse\Response      as RelationRefuseResponse,
    Zhanhd\ReqRes\Relation\BreakOff\Response    as RelationBreakOffResponse,
    Zhanhd\ReqRes\Relation\FriendShipUpdateResponse,
    Zhanhd\ReqRes\Invite\NotifyResponse         as InviteNotifyResponse,
    Zhanhd\ReqRes\SysMsgResponse;

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,
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
$robot->on(9, function($c, $qargv) {
    $c->send(Robot::encodeWithHeader(36));
});

/**
 *
 */
$robot->on(37, function($c, $qargv) {
    $res = new HeartBeatResponse;
    $res->decode($qargv);
    print_r($res);
    
    sleep(10);
    $c->send(Robot::encodeWithHeader(36));
});

/**
 * on reward mail notify
 */
$robot->on(110, function($c, $qargv) {
    $res = new RewardMailResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on message mail notify
 */
$robot->on(55, function($c, $qargv) {
    $res = new MessageMailResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on deposit response
 */
$robot->on(85, function($c, $qargv) {
    $res = new DepositResponse;
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

/**
 * on relation refuse response
 */
$robot->on(121, function($c, $qargv) {
    $res = new RelationRefuseResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on relation confirm response
 */
$robot->on(122, function($c, $qargv) {
    $res = new RelationConfirmResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on relation breakoff response
 */
$robot->on(124, function($c, $qargv) {
    $res = new RelationBreakOffResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on relation friendship update response
 */
$robot->on(127, function($c, $qargv) {
    $res = new FriendShipUpdateResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on relation communicate response
 */
$robot->on(65, function($c, $qargv) {
    $res = new RelationCommunicateResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on invite notify response
 */
$robot->on(133, function($c, $qargv) {
    $res = new InviteNotifyResponse;
    $res->decode($qargv);
    print_r($res);
});

/* on sysmsg response */
$robot->on(219, function($c, $qargv) {
    $res = new SysMsgResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

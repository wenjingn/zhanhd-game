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
    Zhanhd\ReqRes\MessageMail\NotifyResponse as MessageNotifyResponse,
    Zhanhd\ReqRes\RewardMail\NotifyResponse  as RewardNotifyResponse,
    Zhanhd\ReqRes\Guild\Apply\Notify         as GuildApplyNotify,
    Zhanhd\ReqRes\Guild\Pending\Approve\Notify  as GuildPendingApproveNotify,
    Zhanhd\ReqRes\Guild\Manage\Expel\Response as GuildManageExpelResponse,
    Zhanhd\ReqRes\Guild\Manage\Appoint\Response as GuildManageAppointResponse,
    Zhanhd\ReqRes\Guild\Manage\Bulletin\Response as GuildBulletinNotify,
    Zhanhd\ReqRes\Guild\Manage\Transfer\Response as GuildManageTransferResponse,
    Zhanhd\ReqRes\Guild\Member\Quit\Response as GuildMemberQuitResponse,
    Zhanhd\ReqRes\Guild\Impeach\Response     as GuildImpeachResponse,
    Zhanhd\ReqRes\Guild\Impeach\NewPresidentNotify;

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
    $res = new InitResponse;
    $res->decode($qargv);
    print_r($res);
    var_dump($res->user->secret->strval());
});

/**
 * on mail notify response
 */
$robot->on(55, function($c, $qargv) {
    $res = new MessageNotifyResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on reward reward response
 */
$robot->on(110, function($c, $qargv) {
    $res = new RewardNotifyResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 * on accum desposit response
 */
$robot->on(128, function($c, $qargv) {
    $res = new Zhanhd\ReqRes\AccumDepositResponse;
    $res->decode($qargv);
    print_r($res);
});

/* on achievement response */
$robot->on(74, function($c, $qargv) {
    $res = new Zhanhd\ReqRes\Achievement\UpdateResponse;
    $res->decode($qargv);
    print_r($res);
});

/* on new zone mission response */
$robot->on(156, function($c, $qargv) {
    $res = new Zhanhd\ReqRes\NewzoneMission\UpdateResponse;
    $res->decode($qargv);
    print_r($res);
});

/* on guild bulletin notify */
$robot->on(191, function($c, $qargv) {
    $res = new GuildBulletinNotify;
    $res->decode($qargv);
    print_r($res);
});

/* on guild apply notify */
$robot->on(196, function($c, $qargv) {
    $res = new GuildApplyNotify;
    $res->decode($qargv);
    print_r($res);
});

/* on guild pending approve notify */
$robot->on(195, function($c, $qargv){
    $res = new GuildPendingApproveNotify;
    $res->decode($qargv);
    print_r($res);
});

/* on guild manage expel notify */
$robot->on(202, function($c, $qargv){
    $res = new GuildManageExpelResponse;
    $res->decode($qargv);        
    print_r($res);
});

/* on guild manage appoint notify  */
$robot->on(204, function($c, $qargv){
    $res = new GuildManageAppointResponse;
    $res->decode($qargv);
    print_r($res);
});

/* on guild manage transfer notify */
$robot->on(206, function($c, $qargv) {
    $res = new GuildManageTransferResponse;
    $res->decode($qargv);
    print_r($res);
});

/* on guild member quit notify */
$robot->on(208, function($c, $qargv) {
    $res = new GuildMemberQuitResponse;
    $res->decode($qargv);
    print_r($res);
});

/* on guild impeach response */
$robot->on(212, function($c, $qargv) {
    $res = new GuildImpeachResponse;
    $res->decode($qargv);
    print_r($res);
});

/* on guild impeach president success notify */
$robot->on(220, function($c, $qargv) {
    $res = new NewPresidentNotify;
    $res->decode($qargv);
    print_r($res);
});


/**
 *
 */
$robot->connect($config->host, $config->port);

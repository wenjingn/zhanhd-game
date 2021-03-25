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
    Zhanhd\ReqRes\Account\InitResponse;

/**
 *
 */
use Zhanhd\ReqRes\Guide\Request  as GuideRequest,
    Zhanhd\ReqRes\Guide\Response as GuideResponse,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\ReqRes\Hero\Enhance\Response as HeroEnhanceResponse,
    Zhanhd\ReqRes\Hero\Upgrade\Response as HeroUpgradeResponse,
    Zhanhd\ReqRes\Hero\Transfer\Response as HeroTransferResponse,
    Zhanhd\ReqRes\Crusade\Attack\Response as CrusadeAttackResponse;

/**
 *
 */
class RobotGuideRequest extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(93);
        $this->attach('guide', new GuideRequest);
    }
}

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,
    'guideId' => false,
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
    $req = new RobotGuideRequest;
    $req->guide->guideId->intval($config->guideId);
    $c->send($req->encode());
});

/**
 * guide response
 */
$robot->on(94, function($c, $qargv) {
    $res = new GuideResponse;
    $res->decode($qargv);
    print_r($res);
});

/* building resource response */
$robot->on(11, function($c, $qargv) {
    $res = new ResourceResponse;
    $res->decode($qargv);
    print_r($res);
});

/* hero enhance response */
$robot->on(57, function($c, $qargv) {
    $res = new HeroEnhanceResponse;
    $res->decode($qargv);
    print_r($res);
});

/* hero upgrade response */
$robot->on(114, function($c, $qargv) {
    $res = new HeroUpgradeResponse;
    $res->decode($qargv);
    print_r($res);
});

/* hero transfer response */
$robot->on(98, function($c, $qargv) {
    $res = new HeroTransferResponse;
    $res->decode($qargv);
    print_r($res);
});

/* crusade attack response */
$robot->on(21, function($c, $qargv) {
    $res = new CrusadeAttackResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

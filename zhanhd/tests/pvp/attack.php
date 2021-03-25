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
    Zhanhd\ReqRes\PvpRank\Attack\Request  as AttackRequest,
    Zhanhd\ReqRes\PvpRank\Attack\Response as AttackResponse;

/**
 *
 */
class RobotAttackRequest extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(43);
        $this->attach('attack', new AttackRequest);
    }
}

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'pid'  => false,
    'rank' => false,
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
    $req = new RobotAttackRequest;
    $req->attack->pid->intval($config->pid);
    $req->attack->rank->intval($config->rank);
    $c->send($req->encode());
});

/**
 *
 */
$robot->on(44, function($c, $qargv) {
    $res = new AttackResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

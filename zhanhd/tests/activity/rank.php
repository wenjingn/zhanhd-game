<?php
/**
 * $Id$
 */

/**
 *
 */
require '/data/php/games/zhanhd/tests/Robot.php';

/**
 *
 */
use System\Stdlib\Object,
    System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\Account\Signin\Request as SigninRequest,
    Zhanhd\ReqRes\Activity\Rank\Request  as ActivityRankRequest,
    Zhanhd\ReqRes\Activity\Rank\Response as ActivityRankResponse;

/**
 *
 */
class RobotSigninRequest extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(4);
        $this->attach('signin', new SigninRequest);
    }
}

/**
 *
 */
class RobotActivityRankRequest extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(91);
        $this->attach('rank', new ActivityRankRequest);
    }
}

/**
 *
 */
$config = (new Object)->import(getlongopt([
    'host' => '192.168.1.186',
    'port' => 14924,

    'username' => false,
    'password' => false,

    'aid' => false,
]));

/**
 *
 */
$robot = new Robot;

/**
 *
 */
$robot->on('connect', function ($c) use ($config) {
    $request = new RobotSigninRequest;
    $request->signin->login->strval($config->username);
    $request->signin->passwd->strval($config->password);
    $c->send($request->encode());
});

/**
 *
 */
$robot->on(6, function ($c) use ($config) {
    $request = new RobotActivityRankRequest;
    $request->rank->aid->intval($config->aid);
    $c->send($request->encode());
});

/**
 *
 */
$robot->on(92, function ($c, $data) {
    $response = new ActivityRankResponse;
    $response->decode($data);
    print_r($response);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

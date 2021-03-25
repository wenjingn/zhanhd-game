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
    System\Swoole\ReqResHeader;

/**
 *
 */
use Zhanhd\ReqRes\Account\Signin\Request    as SigninRequest,
    Zhanhd\ReqRes\Activity\Instance\Request  as ActivityInstanceRequest,
    Zhanhd\ReqRes\Activity\Instance\Response as ActivityInstanceResponse,
    Zhanhd\ReqRes\HeroUpgradeResponse;

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
class RobotActivityInstanceRequest extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(81);
        $this->attach('activity', new ActivityInstanceRequest);
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

    'aid'  => false,
    'flag' => false,
]));

/**
 *
 */
$robot = new Robot;

/**
 *
 */
$robot->on('connect', function($c) use ($config) {
    $request = new RobotSigninRequest;
    $request->signin->login ->strval($config->username);
    $request->signin->passwd->strval($config->password);
    $c->send($request->encode());
});

/**
 *
 */
$robot->on(6, function($c) use ($config) {
    $request = new RobotActivityInstanceRequest;
    $request->activity->aid->intval($config->aid);
    $request->activity->flag->intval($config->flag);
    $c->send($request->encode());
});

/**
 *
 */
$robot->on(40, function($c, $data) {
    $response = new HeroUpgradeResponse;
    $response->decode($data);
    print_r($response);
});

/**
 *
 */
$robot->on(82, function($c, $data) {
    $response = new ActivityInstanceResponse;
    $response->decode($data);

    //print_r($response);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

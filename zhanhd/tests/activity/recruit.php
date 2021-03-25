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
    Zhanhd\ReqRes\Activity\Recruit\Request  as ActivityRecruitRequest,
    Zhanhd\ReqRes\Activity\Recruit\Response as ActivityRecruitResponse,
    Zhanhd\ReqRes\Recruit\HeroResponse,
    Zhanhd\ReqRes\Recruit\EquipResponse,
    Zhanhd\ReqRes\Recruit\PropResponse,
    Zhanhd\ReqRes\Building\ResourceResponse;

/**
 *
 */
class RobotSigninRequest extends ReqResHeader
{
    /**
     *
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
class RobotActivityRecruitRequest extends ReqResHeader
{
    /**
     *
     */
    protected function setupResponse()
    {
        $this->command->intval(89);
        $this->attach('recruit', new ActivityRecruitRequest);
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
    $request->signin->login ->strval($config->username);
    $request->signin->passwd->strval($config->password);
    $c->send($request->encode());
});

/**
 *
 */
$robot->on(6, function ($c) use ($config) {
    $request = new RobotActivityRecruitRequest;
    $request->recruit->aid->intval($config->aid);
    $c->send($request->encode());
});

/**
 *
 */
$robot->on(90, function ($c, $data) {
    $response = new ActivityRecruitResponse;
    $response->decode($data);
    print_r($response);
});

/**
 *
 */
$robot->on(11, function ($c, $data) {
    $response = new ResourceResponse;
    $response->decode($data);
    print_r($response);
});

/**
 *
 */
$robot->on(13, function ($c, $data) {
    $response = new HeroResponse;
    $response->decode($data);
    print_r($response);
});

/**
 *
 */
$robot->on(28, function ($c, $data) {
    $response = new EquipResponse;
    $response->decode($data);
    print_r($response);
});

/**
 *
 */
$robot->on(78, function ($c, $data) {
    $response = new PropResponse;
    $response->decode($data);
    print_r($response);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

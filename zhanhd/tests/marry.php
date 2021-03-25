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
use Zhanhd\ReqRes\Account\Signin\Request as SigninRequest,
    Zhanhd\ReqRes\Marry\Request          as MarryRequest,
    Zhanhd\ReqRes\Marry\Response         as MarryResponse;

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
class RobotMarryRequest extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(83);
        $this->attach('marry', new MarryRequest);
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

    'peid' => false,
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
$robot->on(6, function ($c, $data) use ($config) {
    $request = new RobotMarryRequest;
    $request->marry->peid->intval($config->peid);
    $c->send($request->encode());
});

/**
 *
 */
$robot->on(84, function ($c, $data) {
    $response = new MarryResponse;
    $response->decode($data);
    print_r($response);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

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
    System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\Account\Signin\Request as SigninRequest,
    Zhanhd\ReqRes\IllustrationResponse;

/**
 *
 */
class RobotSigninRequest extends ReqResHeader
{
    /**
     *
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
class RobotIllustrationRequest extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(51);
    }
}

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host' => '192.168.1.186',
    'port' => 14924,

    'username' => false,
    'password' => false,
)));

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
$robot->on(9, function($c, $qargv) use ($config) {
    $c->send((new RobotIllustrationRequest)->encode());
});

/**
 *
 */
$robot->on(52, function($c, $qargv) {
    print_r((new IllustrationResponse)->decode($qargv));
    $c->close();
});

/**
 *
 */
$robot->connect($config->host, $config->port);

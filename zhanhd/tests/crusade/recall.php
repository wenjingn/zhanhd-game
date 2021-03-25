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
use Zhanhd\ReqRes\Account\Signin\Request  as SigninRequest,
    Zhanhd\ReqRes\Crusade\Recall\Request  as CrusadeRecallRequest,
    Zhanhd\ReqRes\Crusade\Recall\Response as CrusadeRecallResponse;

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
class RobotCrusadeRecallRequest extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(23);
        $this->attach('request', new CrusadeRecallRequest);
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

    'cid' => false,
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
    $request = new RobotCrusadeRecallRequest;
    $request->request->cid->intval($config->cid);

    $c->send($request->encode());
});

/**
 *
 */
$robot->on(24, function($c, $qargv) {
    print_r((new CrusadeRecallResponse)->decode($qargv));
    $c->close();
});

/**
 *
 */
$robot->connect($config->host, $config->port);

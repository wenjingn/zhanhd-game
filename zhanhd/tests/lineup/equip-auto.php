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
use Zhanhd\ReqRes\Account\Signin\Request as SigninRequest,
    Zhanhd\ReqRes\Lineup\EquipAuto       as Request,
    Zhanhd\ReqRes\Lineup\EquipAutoResponse;

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
class RobotEquipAutoRequest extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(45);
        $this->attach('auto', new Request);
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

    'gid' => false,
    'pos' => false,
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
    $request = new RobotEquipAutoRequest;
    $request->auto->gid->intval($config->gid);
    $request->auto->pos->intval($config->pos);

    $c->send($request->encode());
});

/**
 * 
 */
$robot->on(46, function($c, $qargv) {
    print_r((new EquipAutoResponse)->decode($qargv));
    $c->close();
});

/**
 *
 */
$robot->connect($config->host, $config->port);

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
use Zhanhd\ReqRes\Account\Signin\Request  as SigninRequest,
    Zhanhd\ReqRes\Equip\Decompose\Request    as DecomposeRequest,
    Zhanhd\ReqRes\Equip\Decompose\Response   as DecomposeResponse,
    Zhanhd\ReqRes\Building\ResourceResponse;

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
class RobotDecomposeRequest extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(58);
        $this->attach('decompose', new DecomposeRequest);
    }
}

/**
 *
 */
$config = (new Object)->import(getlongopt([
    'host' => '192.168.1.187',
    'port' => 14924,

    'username' => false,
    'password' => false,

    'peids'    => false,
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
    $request->signin->login->strval($config->username);
    $request->signin->passwd->strval($config->password);
    $c->send($request->encode());
});

/**
 *
 */
$robot->on(6, function($c) use ($config) {
    $request = new RobotDecomposeRequest;

    if (!empty($config->peids)) {
        $peids = explode(',', $config->peids);
    } else {
        $peids = [];
    }

    $request->decompose->peids->resize(count($peids));
    foreach ($request->decompose->peids as $k => $o) {
        $o->intval($peids[$k]);
    }
    $c->send($request->encode());
});

/**
 *
 */
$robot->on(59, function($c, $qargv){
    $r = new DecomposeResponse;
    $r->decode($qargv);
    print_r($r);
});

/**
 *
 */
$robot->on(11, function($c, $qargv) {
    $r = new ResourceResponse;
    $r->decode($qargv);
    print_r($r);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

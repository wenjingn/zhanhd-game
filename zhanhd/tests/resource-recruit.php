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
    Zhanhd\ReqRes\ResourceRecruitRequest,
    Zhanhd\ReqRes\ResourceRecruitResponse;

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
class RobotResourceRecruitRequest extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(29);
        $this->attach('recruit', new ResourceRecruitRequest);
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

    'w' => false,
    's' => false,
    'a' => false,
    'h' => false,
)));

/**
 *
 */
$robot = new Robot;

/**
 *
 */
$robot->on(9, function($c, $qargv) use ($config) {
    $request = new RobotResourceRecruitRequest;
    $request->recruit->soldier->intval($config->s);
    $request->recruit->weapon ->intval($config->w);
    $request->recruit->armor  ->intval($config->a);
    $request->recruit->horse  ->intval($config->h);

    $c->send($request->encode());
});

/**
 *
 */
$robot->on(30, function($c, $qargv) {
    print_r((new ResourceRecruitResponse)->decode($qargv));
    $c->close();
});

/**
 *
 */
$robot->connect($config->host, $config->port);

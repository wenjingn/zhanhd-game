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
    Zhanhd\ReqRes\Relation\Like\Request   as LikeRequest,
    Zhanhd\ReqRes\Relation\Like\Response  as LikeResponse,
    Zhanhd\ReqRes\Relation\FriendShipUpdateResponse;

/**
 *
 */
class RobotLikeRequest extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(66);
        $this->attach('like', new LikeRequest);
    }
}

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'friends' => false,
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
    $friends = explode(',', $config->friends);
    $req = new RobotLikeRequest;
    $req->like->friends->resize(count($friends));
    foreach ($req->like->friends as $i => $o) {
        $o->intval($friends[$i]);
    }
    $c->send($req->encode());
});

/**
 *
 */
$robot->on(67, function($c, $qargv) {
    $res = new LikeResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->on(127, function($c, $qargv) {
    $res = new FriendShipUpdateResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

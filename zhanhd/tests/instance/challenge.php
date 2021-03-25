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
use Zhanhd\Config\Store;

/**
 *
 */
use Zhanhd\ReqRes\Account\Signup\Response as SignupResponse,
    Zhanhd\ReqRes\Account\InitResponse,
    Zhanhd\ReqRes\Task\Request as TaskRequest,
    Zhanhd\ReqRes\Task\FightEventResponse,
    Zhanhd\ReqRes\Task\ResourceEventResponse,
    Zhanhd\ReqRes\Task\BranchEventResponse,
    Zhanhd\ReqRes\Task\UnlockTaskResponse,
    Zhanhd\ReqRes\TaskInfo,
    Zhanhd\ReqRes\Achievement\UpdateResponse,
    Zhanhd\ReqRes\NewzoneMission\UpdateResponse as NewzoneMissionUpdateResponse;

/**
 * init resource
 */
Store::setup(unserialize(file_get_contents('/data/php/games/zhanhd/cache/config.data')));

/**
 *
 */
$config = (new Object)->import(getlongopt(array(
    'host'  => '192.168.1.186',
    'port'  => 14924,
    'login' => 1,

    'diff' => false,

    'ins'   => 10101,
    'final' => 0,
    'path'  => 0,
    'step'  => 0,
    'over'  => 0,
)));

function runTask($c, $config)
{
    static $paths = [];

    if ($config->over) {
        $c->close();
        return;
    }

    if (false === isset($paths[$config->ins])) {
        $paths[$config->ins] = Store::get('ins'.$config->diff, $config->ins)->getAllPath();
    }

    displayRun($config, $paths);
    $req = new TaskRequest;
    $req->gid->intval(1);
    $req->task->setFightId($config->ins);
    $req->task->eid->intval($paths[$config->ins][$config->path][$config->step]);
    $req->task->flag->intval($config->diff);
    $c->send(Robot::encodeWithHeader(16, $req));
    
    $config->step++;
    if ($config->step == count($paths[$config->ins][$config->path])) {
        $config->step = 0;
        $config->path++;
    }
    if ($config->path == count($paths[$config->ins])) {
        $config->path = 0;
        $currFight = Store::get('ins'.$config->diff, $config->ins);
        if (isset($currFight->next)) {
            $config->ins = $currFight->next;
        }

        if (false === isset($currFight->next) || $config->ins == $config->final) {
            $config->over = true;
        }
    }
}

function displayRun($config, $paths)
{
    if ($config->step == 0 && $config->path == 0) {
        printf("[fight:%d]\n", $config->ins);
    }
    printf("%d ", $paths[$config->ins][$config->path][$config->step]);
    if ($config->step == count($paths[$config->ins][$config->path])-1) {
        printf("\n");
    }
}

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
    runTask($c, $config);
});

/**
 * fight event response
 */
$robot->on(17, function($c, $qargv) use ($config) {
    $res = new FightEventResponse;
    $res->decode($qargv);
    if ($res->combat->win->intval()) {
        runTask($c, $config);
    } else {
        printf('task failure on %d %d %d', $config->ins, $config->path, $config->step);
    }
});

/**
 * resource event response
 */
$robot->on(18, function($c, $qargv) use ($config) {
    runTask($c, $config);
});

/**
 * branch event response
 */
$robot->on(33, function($c, $qargv) use ($config) {
    runTask($c, $config);
});

/**
 * random event response
 */
$robot->on(19, function($c, $qargv) use ($config) {
    runTask($c, $config);
});

/**
 * achievement notify response
 */
$robot->on(74, function($c, $qargv) {
    $res = new UpdateResponse;
    $res->decode($qargv);
});

/* new zone mission response */
$robot->on(156, function($c, $qargv){
    $res = new NewzoneMissionUpdateResponse;
    $res->decode($qargv);
    print_r($res);
});

/**
 *
 */
$robot->connect($config->host, $config->port);

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
$config = (new Object)->import(getlongopt(array(
    'host' => '127.0.0.1',
    'port' => 46420,
)));

/**
 *
 */
$robot = new Robot(false);

/**
 *
 */
$robot->on('connect', function($c) use ($config) {
    $request = new ReqResHeader;
    $request->command->intval(60001);

    $c->send($request->encode());
});

/**
 *
 */
$robot->connect($config->host, $config->port);

<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Swoole\Client;

/**
 *
 */
use Zhanhd\Config\Store;
use Zhanhd\Extension\TaskEmitter;
use Zhanhd\Extension\Activity\Module;
use Zhanhd\Extension\Test;

function test($g)
{
    var_dump($ret);
}

/**
 *
 */
return function(Client $c) {
    $swServer = $this->getServer();
    print_r($swServer->clients);
};

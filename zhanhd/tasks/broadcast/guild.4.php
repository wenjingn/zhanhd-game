<?php
/**
 * $Id$
 */

/**
 *
 */
use Zhanhd\Task\Guild\Request;

/**
 *
 */
return function($data){
    $req = new Request;
    $req->decode($data);
    $data = substr($data, $req->length());
    $gid = $req->gid->intval();
    $set = $this->redis->hgetall(sprintf('zhanhd:ht:guildmembers:%d', $gid));
    $swServer = $this->getServer()->getSwooleServer();
    foreach ($set as $pid => $fd) {
        $swServer->send($fd, $data);
    }
};

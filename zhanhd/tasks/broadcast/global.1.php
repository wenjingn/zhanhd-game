<?php
/**
 * $Id$
 */

/**
 *
 */
return function($data) {
    $this->getServer()->scanFds(function($s, $fd, $data){
        $s->swServer->send($fd, $data);
    }, $data);
};

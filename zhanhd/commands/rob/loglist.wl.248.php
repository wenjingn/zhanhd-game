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
use Zhanhd\ReqRes\Rob\Log\Response,
    Zhanhd\Object\Player\Rob\Log;

/**
 *
 */
return function(Client $c){
    if (-1 === $this->parseParametersNone($c)) {
        return;
    }

    $p = $c->local->player;
    $logs = Log::getsByPid($this->pdo, $p->id, $this->ustime);
    $r = new Response;
    $r->logs->resize($logs->count());
    foreach ($r->logs as $i => $o) {
        $o->fromObject($logs->get($i));
    }
    $c->addReply($r);
};

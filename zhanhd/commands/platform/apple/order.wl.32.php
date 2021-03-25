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
use Zhanhd\ReqRes\Platform\Apple\Order\Request,
    Zhanhd\Platform\Apple\Task\Order\Request as TaskRequest,
    Zhanhd\Object\OrderWaiting;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $taskReq = new TaskRequest;
    $taskReq->pid->intval($c->local->player->id);
    $taskReq->serial->strval($request->orderSerial->strval());
    $this->task('apple-order', $taskReq);
    $c->addReply($this->errorResponse->error('sdk communication failure waiting patient'));
};

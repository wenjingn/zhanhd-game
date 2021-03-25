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
use Zhanhd\Object\Order, 
    Zhanhd\ReqRes\HeartBeatResponse,
    Zhanhd\Extension\Mail\Module    as MailModule,
    Zhanhd\Extension\Deposit\Module as DepositModule,
    Zhanhd\Extension\Relation\Module as RelationModule,
    Zhanhd\Extension\Top32;


/**
 *
 */
return function(Client $c) {
    if (-1 === $this->parseParametersNone($c)) {
        return;
    } 

    // sending heartbeat response
    $r = new HeartBeatResponse;
    $c->addReply($r);

    // todo
    
    if ($c->flags->bithas(Client::FLAG_CLOSING)) {
        return;
    } else if ($c->login->intval() == 0) {
        return;
    }

    /* notify logged user */
    if ($c->login->intval() == 0 || $c->local->player === null || false === $c->local->player->userValidateStatus()) {
        return;
    }

    MailModule::aspect($c, $this);

    $orders = Order::getsByPid($this->pdo, $c->login->intval());
    foreach ($orders as $order) {
        $order->status = Order::STATUS_SUCCESS;
        $order->save();

        DepositModule::aspect($c, $order, $this);
    }

    $m = new Top32($this);
    $m->globalMsg($this);
};

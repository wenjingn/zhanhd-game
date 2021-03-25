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
use Zhanhd\ReqRes\Platform\Baidu\Order\Request,
    Zhanhd\ReqRes\DepositResponse,
    Zhanhd\Config\Store,
    Zhanhd\Object\Order,
    Zhanhd\Library\Sdk\Baidu,
    Zhanhd\Extension\Deposit\Module as DepositModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;

    $order = new Order;
    if (false === $order->findBySerial($request->serial->strval())) {
        return $c->addReply($this->errorResponse->error('order not found'));
    }

    if ($order->pid != $p->id) {
        return $c->addReply($this->errorResponse->error('no privilege'));
    }

    if ($order->status > Order::STATUS_READYON) {
        return $c->addReply($this->errorResponse->error('order already completed'));
    }

    $merchandise = Store::get('merchandise', $order->merchandise);
    if ($order->status == Order::STATUS_READYON) {
        $order->status = Order::STATUS_SUCCESS;
        $order->save();
        return DepositModule::aspect($c, $order, $this);
    }
    
    $sdk = new Baidu;
    if (false === $sdk->orderQuery($order->serial, $ret)) {
        return $c->addReply($this->errorResponse->error('sdk communication failure'));
    }

    if ($ret->ResultCode != 1) {
        return $c->addReply($this->errorResponse->error('sdk authentication failure'));
    }

    $content = urldecode($ret->Content);
    if ($ret->Sign != $sdk->signMd5($ret->ResultCode, $content)) {
        return $c->addReply($this->errorResponse->error('sdk sign error'));
    }

    $ret = json_decode(base64_decode($content));
    if ($order->serial != $ret->CooperatorOrderSerial) {
        return $c->addReply($this->errorResponse->error('order-info abnormal serial'));
    }

    if (false === $merchandise->checkMoney($ret->OrderMoney, false)) {
        return $c->addReply($this->errorResponse->error('order-info abnormal money'));
    }

    $u = $p->getUser();
    if ($ret->Uid != $u->login) {
        return $c->addReply($this->errorResponse->error('order-info abnormal uid'));
    }

    switch ($ret->OrderStatus) {
    case 0:
        $order->status = Order::STATUS_WAITING;
        $order->save();
        return $c->addReply($this->errorResponse->error('the money not arrived yet'));
    case 1:
        $order->status = Order::STATUS_SUCCESS;
        $order->save();
        return DepositModule::aspect($c, $merchandise, $this);
    case 2:
        $order->status = Order::STATUS_FAILURE;
        $order->save();
        return $c->addReply($this->errorResponse->error('trading failure'));
    }
};

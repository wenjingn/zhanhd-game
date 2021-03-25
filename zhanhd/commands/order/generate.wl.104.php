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
use Zhanhd\ReqRes\Order\Generate\Request,
    Zhanhd\ReqRes\Order\Generate\Response,
    Zhanhd\Config\Store,
    Zhanhd\Object\Order;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    
    $merchandise = $request->merchandise->intval();
    if (false === Store::has('merchandise', $merchandise)) {
        return $c->addReply($this->errorResponse->error('merchandise invalid'));
    }

    $order = new Order;
    $order->serial = Order::generate($this->ustime, $p->zone);
    $order->pid = $p->id;
    $order->merchandise = $merchandise;
    $order->status = Order::STATUS_INITIAL;
    $order->save();

    $r = new Response;
    $r->merchandise->intval($order->merchandise);
    $r->order->strval($order->serial);
    $c->addReply($r);
};

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
use Zhanhd\ReqRes\Platform\Tencent\Order\Request,
    Zhanhd\ReqRes\Platform\Tencent\BalanceResponse,
    Zhanhd\ReqRes\Platform\Tencent\Pay\Response,
    Zhanhd\Config\Store,
    Zhanhd\Object\User,
    Zhanhd\Library\Sdk\QQ,
    Zhanhd\Library\Sdk\WeChat,
    Zhanhd\Extension\Deposit\Module as DepositModule,
    Zhanhd\Object\Order;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    if (null === ($merchandise = Store::get('merchandise', $request->merchandise->intval()))) {
        return $this->errorResponse->error('merchandise invalid');
    }

    $p = $c->local->player;
    $u = new User;
    $u->find($c->uid->intval());
    if ($u->platform != User::PF_QQ && $u->platform != User::PF_WECHAT) {
        return $c->addReply($this->errorResponse->error('invalid platform'));
    }

    if ($u->platform == User::PF_QQ) {
        $sdk = new QQ;
    } else if ($u->platform == User::PF_WECHAT) {
        $sdk = new WeChat;
    }

    if (false === $sdk->pay($u->login, $u->passwd, $u->profile->payToken, $u->profile->pf, $u->profile->pfkey, $p->zone, $merchandise->price, $ret)) {
        return $c->addReply($this->errorResponse->error('sdk communication failure'));
    }

    if ($ret->ret != 0) {
        if ($ret->ret == 1004) {
            return $c->addReply(new Response);
        }

        if ($ret->ret == 1018) {
            return $c->addReply($this->errorResponse->error('sdk authentication failure'));
        }

        return $c->addReply($this->errorResponse->error('sdk payment failure'));
    }


    $r = new BalanceResponse;
    $r->balance->intval($ret->balance);
    $c->addReply($r);

    $order = new Order;
    $order->serial = $ret->billno;
    $order->pid = $p->id;
    $order->merchandise = $merchandise->id;
    $order->status = Order::STATUS_SUCCESS;
    $order->save();

    DepositModule::aspect($c, $order, $this);
};

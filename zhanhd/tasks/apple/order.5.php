<?php
/**
 * $Id$
 */

/**
 *
 */
use Zhanhd\Platform\Apple\Task\Order\Request,
    Zhanhd\Config\Store,
    Zhanhd\Object\Order,
    Zhanhd\Object\OrderWaiting,
    Zhanhd\Library\Sdk\Apple;

/**
 *
 */
return function($data) {
    $req = new Request;
    $req->decode($data);

    $pid    = $req->pid->intval();
    $serial = $req->serial->strval();
    $sdk = new Apple;
    if (false === $sdk->orderQuery($serial, $ret)) {
        $ow = new OrderWaiting;
        $uniqrec = md5($serial);
        if (false === $ow->findByUniqrec($uniqrec)) {
            $ow->uniqrec = $uniqrec;
            $ow->pid = $pid;
            $ow->receipt = $serial;
            $ow->save();
        }
        return;
    }

    if ($ret->status) {
        return;
    }

    if (null === ($merch = Store::get('merchandise', Apple::getMerchId($ret)))) {
        return;
    }

    $order = new Order;
    if (false === $order->findBySerial($ret->receipt->transaction_id)) {
        $order->serial = $ret->receipt->transaction_id;
        $order->pid    = $pid;
        $order->merchandise = $merch->id;
    }

    if ($order->status >= Order::STATUS_READYON) {
        return;
    }
    $order->status = Order::STATUS_READYON;
    $order->save();
    
    $ow = new OrderWaiting;
    $uniqrec = md5($serial);
    if (false === $ow->findByUniqrec($uniqrec)) {
        $ow->uniqrec = $uniqrec;
        $ow->pid = $pid;
        $ow->receipt = $serial;
    }
    $ow->flag = OrderWaiting::FLAG_DONE;
    $ow->save();
};

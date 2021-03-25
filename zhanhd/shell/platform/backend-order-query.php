<?php
/**
 * $Id$
 */

/**
 *
 */
require '/data/php/games/zhanhd/shell/bootstrap.php';

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Object\Order,
    Zhanhd\Library\Sdk\Baidu;

/**
 *
 */
define('PAGE_LENGTH', 20);

/**
 *
 */
$pdo = $boot->globals->pdo;
$sdk = new Baidu;
$tim = 0;
while (true) {
    $total = Order::getTotal($pdo);
    $ustime = ustime();
    $boot->log(1, '%d : %d records processing', ++$tim, $total);
    for ($i = 0; $i < $total; $i += PAGE_LENGTH) {
        $orders = Order::getPage($pdo, $i, PAGE_LENGTH);
        foreach ($orders as $order) {
            if (false === $sdk->orderQuery($order->serial, $ret)) {
                continue;
            }

            if ($ret->ResultCode != 1) {
                // 当百度订单还未生成时
                if ($ustime - $order->created > 3600000000) {
                    // 超过一小时百度订单还未生成,关闭订单
                    $order->status = Order::STATUS_CLOSED;
                    $order->save();
                }
                continue;
            }

            $content = urldecode($ret->Content);
            if ($ret->Sign != $sdk->signMd5($ret->ResultCode, $content)) {
                continue;
            }
            
            $ret = json_decode(base64_decode($content));
            if ($order->serial != $ret->CooperatorOrderSerial) {
                continue;
            }

            $merchandise = Store::get('merchandise', $order->merchandise);
            if (false === $merchandise->checkMoney($ret->OrderMoney, true)) {
                continue;
            }

            switch ($ret->OrderStatus) {
            case 0:
                if ($order->status == Order::STATUS_WAITING) {
                    if ($ustime - $order->updated > 86400000000 * 2) {
                        // 假如等待时间大于2天,还没有到账,关闭订单
                        $order->status = Order::STATUS_CLOSED;
                        $order->save();
                    }
                }

                if ($order->status == Order::STATUS_INITIAL) {
                    $order->status = Order::STATUS_WAITING;
                    $order->save();
                }
                break;
            case 1:
                $order->status = Order::STATUS_READYON;
                $order->save();
                break;
            case 2:
                $order->status = Order::STATUS_FAILURE;
                $order->save();
                break;
            }
        }
    }

    sleep(600);
}

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
    Zhanhd\Object\OrderWaiting,
    Zhanhd\Library\Sdk\Apple;

/**
 *
 */
define('PAGE_LENGTH', 20);

/**
 *
 */
$pdo = $boot->globals->pdo;
$sdk = new Apple;

$total = OrderWaiting::getTotal($pdo);
$ustime = ustime();
for ($i = 0; $i < $total; $i += PAGE_LENGTH) {
    $orders = OrderWaiting::getPage($pdo, $i, PAGE_LENGTH);
    foreach ($orders as $order) {
        if (false === $sdk->orderQuery($order->receipt, $ret)) {
            continue;
        }

        if ($ret->status) {
            continue;
        }

        $o = new Order;
        if ($o->findBySerial($ret->receipt->transaction_id)) {
            $order->flag = 1;
            $order->save();
            continue;
        }

        $o->serial = $ret->receipt->transaction_id;
        $o->pid = $order->pid;
        $o->merchandise = Apple::getMerchId($ret);
        $o->status = Order::STATUS_READYON;
        $o->save();
        $order->flag = 1;
        $order->save();
    }
}

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
use Zhanhd\Config\Store;

/**
 *
 */
function testEdrop()
{
    $edrop = Store::get('edrop', 2031);
    $times = 100;
    $star5 = 0;
    $total = 0;
    while ($times--) {
        $ret = $edrop->pick();
        foreach ($ret as $o) {
            if ($o->e->rarity == 5) {
                $star5 += $o->n;
            }
            $total += $o->n;
        }
    }
    printf("%d:%d\n", $star5, $total);
}

testEdrop();

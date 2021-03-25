<?php
/**
 * $Id$
 */

/**
 *
 */
require '/data/php/games/zhanhd/shell/bootstrap.php';
require '/data/php/games/zhanhd/performance/runtime.php';

/**
 *
 */
use System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Config\Entity,
    Zhanhd\Object\Player;

/**
 *
 */
$config = (new Object)->import(getlongopt([
    'pid' => false,
]));

/**
 *
 */
$o = new Object;
$entities = Store::get('entity');
foreach ($entities as $e) {
    if ($e->type != Entity::TYPE_HERO) {
        continue;
    }   
    if (isset($e->property->npc)) {
        continue;
    }   
    if (isset($e->property->dynasty) && $e->property->dynasty >= 11051) {
        continue;
    }   

    $o->set($e->id, [
        'e' => $e, 
        'n' => 1,
    ]); 
}

var_dump(count($o));
$p = new Player;
$p->find($config->pid);
$run = new Runtime;

$p->increaseEntity($o);

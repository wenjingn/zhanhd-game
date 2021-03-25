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
    Zhanhd\Object\User,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Entity     as PlayerEntity,
    Zhanhd\Extension\Service\Module as ServiceModule;

/**
 *
 */
$argvs = (new Object)->import(getlongopt([
    'count' => false,
    'zone'  => false,
    'prefix' => false,
]));

/**
 *
 */
$g = $boot->globals;

$i = 1;
while ($argvs->count > 0) {
    while (true) {
        $u = new User;
        $account = sprintf('%s%d', $argvs->prefix, $i++);
        if ($u->findByLogin(User::PF_ZHANHD, $account) === false) {
            break;
        }
    }

    $r = new Runtime(sprintf('create user `%s`', $account));
    $u->platform = User::PF_ZHANHD;
    $u->login = $account;
    $u->rawpswd = $account;
    $u->save();

    $p = new Player;
    if ($p->nameExists($argvs->zone, $account)) {
        printf("try to create role for account %s, but nickname already exists for it\n", $account);
        continue;
    }

    $p->veryInitEid = 1101108;
    $p->uid = $u->id;
    $p->zone = $argvs->zone;
    $p->name = $account;
    $retry = 10;
    do {
        $invcode = Player::generateInvcode();
        $retry--;
    } while(($invcodeExists = Player::invcodeExists($g->pdo, $invcode)) && $retry > 0);

    if ($invcodeExists) {
        printf("try failure for account: %s\n", $account);
        continue;
    }

    try {
        $p->invcode = $invcode;
        $p->save();
    } catch (PDOException $e) {
        printf("try failure for account: %s\n", $account);
        continue;
    }

    ServiceModule::forGreener($p, $g);
    callback($p, $g);
    unset($r);
    $argvs->count--;
}

function callback($p, $g) {
    $o = new Object;
    $entities = Store::get('entity');
    foreach ($entities as $e) {
        switch ($e->type) {
        case Entity::TYPE_RESOURCE:
        case Entity::TYPE_MONEY:
            $o->set($e->id, [
                'e' => $e,
                'n' => 100000,
            ]);
            break;
        case Entity::TYPE_HERO:
            if (isset($e->property->npc)) {
                break;
            }
            if (isset($e->property->dynasty) && $e->property->dynasty >= 11051) {
                break;
            }
            if ($e->rarity < 4) {
                break;
            }
            $o->set($e->id, [
                'e' => $e,
                'n' => 1,
            ]);
            break;
        }
    }
    $p->increaseEntity($o, function($pe){
        if ($pe instanceof PlayerEntity) {
            $pe->lvl = 90;
            $pe->save();
        }
    });
}

printf("rebuild-player-rank\n");
`/usr/local/php/bin/php /data/php/games/zhanhd/shell/rebuild-player-rank.php`;

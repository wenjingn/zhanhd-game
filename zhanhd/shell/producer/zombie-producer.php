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
use System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Extension\PvpRank\Module as PvpRankModule,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Lineup as PlayerLineup,
    Zhanhd\Object\Player\Entity as PlayerEntity;

/**
 *
 */
$zombieData    = include '/data/php/games/zhanhd/cache/zombie.data.php';
$randmap       = [];
$formationmap  = [];
{
    foreach (Store::get('formation') as $f) {
        $formationmap[] = $f->id;
    }
}
$nicknameStore = json_decode(file_get_contents('/data/php/games/zhanhd/cache/NickName.json'));
$argvs = (new Object)->import(getlongopt([
    'zone' => false,            
]));

function randNickname(){
    global $nicknameStore;
    global $randmap;
    $firstNameCount = count($nicknameStore->firstNames);
    $lastNameCount = count($nicknameStore->lastNames);

    $firstKey = rand(0, $firstNameCount - 1);
    $lastKey = rand(0, $lastNameCount - 1);

    $firstName = $nicknameStore->firstNames[$firstKey];
    $lastName = $nicknameStore->lastNames[$lastKey];
    $name = $firstName.$lastName;
    if (isset($randmap[$name])) {
        return randNickname();
    }
    $randmap[$name] = 1;
    return $name;
}

function randLeader($p) {
    $sex = mt_rand(0, 1);
    foreach (Store::get('leader', $sex) as $part => $map) {
        $map = array_keys($map);
        $p->profile->$part = $map[mt_rand(0, count($map)-1)];
    }
    $p->save();
}

function randFormation() {
    global $formationmap;
    $rand = mt_rand(0, count($formationmap)-1);
    return $formationmap[$rand];
}

function randHero($data){
    $weightsum = array_sum($data['weights']);
    $rand = mt_rand(1, $weightsum);
    foreach ($data['weights'] as $star => $weight) {
        if ($rand <= $weight) {
            break;
        }
        $rand -= $weight;
    }

    $eid = Store::get('egroup', 302000+$star)->pickone();
    return Store::get('entity', $eid);
}

$i = 1;
foreach ($zombieData as $data) {
    while ($i <= $data['rank']) {
        $p = new Player;
        $p->uid = 0;
        $p->zone = $argvs->zone;
        $p->name = randNickname();
        $p->invcode = Player::generateInvcode();
        $p->save();
        printf("zombie add: %d %s\n", $p->id, $p->name);

        randLeader($p);

        $pl = new PlayerLineup;
        $pl->pid = $p->id;
        $pl->gid = 1;
        $pl->fid = randFormation();
        $pl->save();
        $heros = [];
        for ($j = 0; $j < 6; $j++) {
            while (true) {
                $e = randHero($data);
                if (false === isset($heros[$e->id])) {
                    $heros[$e->id] = 1;
                    break;
                }
            }
            $pe = new PlayerEntity;
            $pe->e = $e;    
            $pe->pid = $p->id;
            $pe->eid = $e->id;
            $pe->save();
            printf("peid:%d eid:%d tag:%s star:%d\n", $pe->id, $pe->e->id, $pe->e->tag, $pe->e->rarity);
            $pl->heros->get($j)->peid = $pe->id;
            $pl->heros->get($j)->save();
            $pe->flags = PlayerEntity::FLAG_INUSE;
            $pe->lvl = $data['lvl'];
            $pe->property->elvl = $data['elvl'];
            
            foreach ($e->army as $aid => $unlock) {
                if ($pe->lvl >= $unlock) {
                    $armyId = $aid;
                } else {
                    break;
                }
            }
            $pe->property->aid = $armyId;
            $pe->save();
        }

        (new PvpRankModule)->push($p);
        $i++;
    }
}

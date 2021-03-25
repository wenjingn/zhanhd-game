<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Service;

/**
 *
 */
use System\Stdlib\Object,
    System\Swoole\Client;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Config\Instance,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Entity   as PlayerEntity,
    Zhanhd\Object\Player\Lineup   as PlayerLineup,
    Zhanhd\Object\Player\Building as PlayerBuilding;

/**
 *
 */
class Module
{
    /**
     * @const integer
     */
    const GreenerCaptainEid = 1101108;
    const GreenerEquipPackageCapacity = 50;
    const GreenerHeroPackageCapacity  = 50;

    /**
     * @var GreenerInitResources
     */
    private static $greenerInitResources = [
        2  => 200,
        3  => 200,
        4  => 200,
        6  => 100,
        7  => 200,
        10 => 100,

        410106 => 5,
        410103 => 5,
        410102 => 5,

        210101 => 1,
        419001 => 1,
    ];

    /**
     * @param Player $p
     * @param Object $globals
     * @return void
     */
    public static function forGreener(Player $p, $globals)
    {
        /* initial buildings */
        foreach (Store::get('building') as $o) {
            $pb = new PlayerBuilding;
            $pb->pid = $p->id;
            $pb->bid = $o->id;
            $pb->lvl = 1;
            $pb->save();
        }

        /* initial lineups */
        $e = Store::get('entity', self::GreenerCaptainEid);
        foreach (PlayerLineup::$groups as $gid) {
            $pl = new PlayerLineup;
            $pl->pid = $p->id;
            $pl->gid = $gid;
            $pl->fid = 140001;

            if ($gid == 1 && $e) {
                $p->increaseEntity((new Object)->import([
                    $e->id => [
                        'e' => $e,
                        'n' => 1,
                        'ignoreAchievement' => true,
                    ],            
                ]), function($pe, $pl){
                    $pl->veryInitPeid = $pe->id;
                    $pe->flags = PlayerEntity::FLAG_INUSE;
                    $pe->gid = 1;
                    $pe->save();
                }, $pl);
            }

            $pl->save();
        }

        /* initial profile */
        $p->profile->currtask1 = Instance::INIT;
        $p->profile->equipPackageCapacity = self::GreenerEquipPackageCapacity;
        $p->profile->heroPackageCapacity  = self::GreenerHeroPackageCapacity;
        $p->profile->save();
        
        /* initial resource and entity */
        $increases = new Object;
        foreach (self::$greenerInitResources as $eid => $num) {
            if (null === ($e = Store::get('entity', $eid))) {
                continue;
            }

            $increases->set($eid, [
                'e' => $e,
                'n' => $num,
                'ignoreAchievement' => true,
            ]);
        }
        $p->increaseEntity($increases);

        /* for greener guide */
        $obj = new Object;
        $guy = 1101203;
        $obj->set($guy, [
            'e' => Store::get('entity', $guy),
            'n' => 1,
            'ignoreAchievement' => true,
        ]);
        $p->increaseEntity($obj, function($pe) {
            $pe->lvl = 19;
            $pe->exp = Store::get('heroexp', 20)->exp - 450;
            $pe->save();
        });
        $p->profile->guideFlag = 1;
        $p->save();
    }

    /**
     * @param Client $c
     * @param Object $globals
     * @param ReqRes $request
     * @return void
     */
    public static function prop410105(Client $c, $globals, $request)
    {
        $p = $c->local->player;
        $n = $request->num->intval();
        $p->profile->equipPackageCapacity += $n*20;
        $p->profile->{410105} -= $n;
        $p->profile->save();
        $r = new \Zhanhd\ReqRes\PropUse\PackageCapacityResponse;
        $r->capacity->intval($p->profile->equipPackageCapacity);
        $c->addReply($r);

        $r = new \Zhanhd\ReqRes\PropUse\PropRemainResponse;
        $r->propId->intval(410105);
        $r->num->intval((int)$p->profile->{410105});
        $c->addReply($r);
    }

    /**
     * @param Client $c
     * @param Object $globals
     * @param ReqRes $request
     * @return void
     */
    public static function prop410210(Client $c, $globals, $request)
    {
        $p = $c->local->player;
        $n = $request->num->intval();
        $p->profile->heroPackageCapacity += $n*20;
        $p->profile->{410210} -= $n;
        $p->profile->save();
        $r = new \Zhanhd\ReqRes\PropUse\HeroPackageCapacityResponse;
        $r->capacity->intval($p->profile->heroPackageCapacity);
        $c->addReply($r);
        
        $r = new \Zhanhd\ReqRes\PropUse\PropRemainResponse;
        $r->propId->intval(410210);
        $r->num->intval((int)$p->profile->{410210});
        $c->addReply($r);
    }

    /**
     * @param Client $c
     * @param Object $globals
     * @param ReqRes $request
     * @return void
     */
    public static function prop410106(Client $c, $globals, $request)
    {
        $p = $c->local->player;
        $l = $p->getLineup($request->gid->intval());
        if (null === $l) {
            return $c->addReply($globals->errorResponse->error('invalid lineup'));
        }
        if (false === $l->getCaptain()) {
            return $c->addReply($globas->errorResponse->error('empty captain'));
        }

        $n = $request->num->intval();
        $energy = $n*100;
        $heros = [];
        foreach ($l->heros as $h) {
            if ($h->peid) {
                $heros[] = $h->pe;
                $h->pe->addEnergy($energy);
            }
        }
        $p->profile->{410106} -= $n;
        $p->profile->save();

        $r = new \Zhanhd\ReqRes\PropUse\HeroEnergyResponse;
        $r->heros->resize(count($heros));
        foreach ($r->heros as $i => $o) {
            $o->fromPlayerEntityObject($heros[$i], $globals);
        }
        $c->addReply($r);
        
        $r = new \Zhanhd\ReqRes\PropUse\PropRemainResponse;
        $r->propId->intval(410106);
        $r->num->intval((int)$p->profile->{410106});
        $c->addReply($r);
    }

    /**
     * @param Client $c
     * @param Object $globals
	 * @param integer $times
     * @return void
     */
    public static function goods203(Client $c, $globals, $times)
    {
        $p = $c->local->player;
        $p->profile->equipPackageCapacity += 20*$times;
        $p->profile->save();
        $r = new \Zhanhd\ReqRes\PropUse\PackageCapacityResponse;
        $r->capacity->intval($p->profile->equipPackageCapacity);
        $c->addReply($r);
        
        $r = new \Zhanhd\ReqRes\Building\ResourceResponse;
        $r->retval->fromOwnerObject($p);
        $c->addReply($r);
    }

    /**
     * @param Client $c
     * @param Object $globals
	 * @param integer $times
     * @return void
     */
    public static function goods215(Client $c, $globals, $times)
    {
        $p = $c->local->player;
        $p->profile->heroPackageCapacity += 20*$times;
        $p->profile->save();
        $r = new \Zhanhd\ReqRes\PropUse\HeroPackageCapacityResponse;
        $r->capacity->intval($p->profile->heroPackageCapacity);
        $c->addReply($r);
        
        $r = new \Zhanhd\ReqRes\Building\ResourceResponse;
        $r->retval->fromOwnerObject($p);
        $c->addReply($r);
    }
}

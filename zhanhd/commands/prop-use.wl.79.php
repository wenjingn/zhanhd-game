<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Swoole\Client,
    System\Stdlib\Object;

/**
 *
 */
use Zhanhd\ReqRes\PropUse\Request   as Request,
    Zhanhd\ReqRes\PropUse\Response  as Response,
    Zhanhd\ReqRes\PropUse\PackageCapacityResponse,
    Zhanhd\ReqRes\PropUse\HeroPackageCapacityResponse,
    Zhanhd\ReqRes\PropUse\HeroEnergyResponse,
    Zhanhd\ReqRes\PropUse\PropRemainResponse,
    Zhanhd\Config\Store,
    Zhanhd\Config\Entity,
    Zhanhd\Extension\Reward\Module  as RewardModule,
    Zhanhd\Extension\Service\Module as ServiceModule;

/**
 *
 */
define('CONFIG_CAPACITY_410105', 20); //库房
define('CONFIG_CAPACITY_410210', 20); //眠床
define('CONFIG_ENERGY_410106',   100); // 木牛流马

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    if (null === ($e = Store::get('entity', $request->propId->intval())) || false === $e->isProp()) {
        return $c->addReply($this->errorResponse->error('notfound prop'));
    }

    $n = $request->num->intval();
    if ($n === 0) {
        return $c->addReply($this->errorResponse->error('invalid prop-num zero'));
    }
    if ($n > $p->profile->{$e->id}) {
        return $c->addReply($this->errorResponse->error('notenough resource'));
    }

    if ($e->type == Entity::TYPE_PROP && false === isset($e->property->service)) {
        return $c->addReply($this->errorResponse->error('unusable prop'));
    }

    if ($e->type == Entity::TYPE_GROWPACK) {
        foreach($e->rules as $diff => $ins);
        if ($diff && $p->profile->{'currtask'.$diff} <= $ins) {
            return $c->addReply($this->errorResponse->error('locked growpack'));
        }
    }

    if ($e->id == 410106) {
        $p->counterCycle->propconsume410106 ++;
        $p->counterCycle->save();

        $p->counter->propconsume410106++;
        $p->counter->save();
    }
    if ($e->type == Entity::TYPE_PROP) {
        $service = 'prop'.$e->id;
        return ServiceModule::$service($c, $this, $request);
    }

    if ($e->type == Entity::TYPE_CHEST) {
        $sources = array();
        foreach ($e->property as $eid => $num) {
            $sources[$eid] = $n * $num;
        }
    } else if ($e->type == Entity::TYPE_RANDPACK) {
        $sources = $e->pick($n);
    } else if ($e->type == Entity::TYPE_GROWPACK) {
        $sources = $e->property;
    }
    $r = new Response;
    $r->propId->intval($e->id);
    RewardModule::aspect($p, $sources, $r->reward, $c, $this);
    $c->addReply($r);
    $p->profile->{$e->id}-= $n;
    $p->profile->save();

    $r = new PropRemainResponse;
    $r->propId->intval($e->id);
    $r->num->intval((integer)$p->profile->{$e->id});
    $c->addReply($r);
};

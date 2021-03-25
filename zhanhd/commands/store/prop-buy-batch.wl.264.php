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
use Zhanhd\ReqRes\Store\Prop\Batch\Request,
    Zhanhd\ReqRes\Store\Prop\Response,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\Config\Store,
    Zhanhd\Extension\Service\Module as ServiceModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }
    
    $p = $c->local->player;
    if (null === ($g = Store::get('propGoods', $request->gid->intval()))) {
        return $c->addReply($this->errorResponse->error('notfound goods'));
    }

	$n = $request->num->intval();
	if ($n == 0) {
		return $c->addReply($this->errorResponse->error('meaningless'));
	}

    $ckey = $g->getCounterKey();
    if ($g->incr) {
		$gold = ($g->price + $g->incr*$p->counterCycle->$ckey)*$n + $g->incr*($n-1)*$n/2;
    } else {
        $gold = $g->price*$n;
    }

    if ($p->gold < $gold) {
        return $c->addReply($this->errorResponse->error('notenough diamond'));
    }

    $p->decrGold($gold);
    $p->counterCycle->$ckey+=$n;
    $p->counter->$ckey+=$n;
    $p->save();

    if (empty($g->eid)) {
        $service = 'goods'.$g->id;
        return ServiceModule::$service($c, $this, $n);
    }

    $rewards = new Object;
    foreach ($g->getRewards() as $eid => $num) {
        $rewards->set($eid, [
            'e' => Store::get('entity', $eid),       
            'n' => $num*$n,
        ]);
    }
    $p->increaseEntity($rewards);

    foreach ($rewards as $ape) {
        $r = new Response;
        $r->retval->fromPlayerEntityObject($ape);
        if ($g->incr) {
            $r->times->intval($p->counterCycle->$ckey);
        }
        $c->addReply($r);
    }

    $r = new ResourceResponse;
    $r->retval->fromOwnerObject($p);
    $c->addReply($r);
};

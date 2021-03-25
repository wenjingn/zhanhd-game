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
use Zhanhd\ReqRes\Equip\Decompose\Request,
    Zhanhd\ReqRes\Equip\Decompose\Response,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\Config\Store,
    Zhanhd\Config\Entity        as SourceEntity,
    Zhanhd\Object\Player\Entity as PlayerEntity;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    
    /**
     * check
     */
    $pes = [];
    foreach ($request->peids as $o) {
        $peid = $o->intval();
        if (isset($pes[$peid])) {
            return $c->addReply($this->errorResponse->error('decompose duplicate pe'));
        }

        $pe = new PlayerEntity;
        if (false === $pe->findByPid($peid, $p->id)) {
            return $c->addReply($this->errorResponse->error('pe not found'));
        }

        if (!$pe->e->salable) {
            return $c->addReply($this->errorResponse->error('unsalable'));
        }

        if ($pe->flags == PlayerEntity::FLAG_INUSE) {
            return $c->addReply($this->errorResponse->error('pe already inuse'));
        }

        if (false === isset($pe->e->decomposed)) {
            return $c->addReply($this->errorResponse->error('miss decomposed list'));
        }

        $pes[$o->intval()] = $pe;
    }

    /**
     * decompose
     */
    $products = new Object;
    $notifyResourceChanged = false;
    foreach ($pes as $pe) {
        foreach ($pe->e->decomposed as $eid => $num) {
            if (null === ($e = Store::get('entity', $eid))) {
                continue;
            }
    
            if ($notifyResourceChanged === false && $e->type == SourceEntity::TYPE_RESOURCE) {
                $notifyResourceChanged = true;
            }

            if (null === ($o = $products->get($eid))) {
                $products->set($eid, [
                    'e' => Store::get('entity', $eid),
                    'n' => $num,
                ]);
            } else {
                $o->n += $num;
            }
        }

        $pe->drop();
    }
    
    $p->increaseEntity($products);

    /**
     * send response
     */
    if ($notifyResourceChanged) {
        $resourceResponse = new ResourceResponse;
        $resourceResponse->retval->fromOwnerObject($p);
        $c->addReply($resourceResponse);
    }

    $r = new Response;
    $r->peids->resize(count($pes));
    reset($pes);
    foreach ($r->peids as $o) {
        $pe = current($pes);
        $o->intval($pe->id);
        next($pes);
    }
    $c->addReply($r);
};

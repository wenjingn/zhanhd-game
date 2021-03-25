<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Swoole\Client;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Config\Entity         as SourceEntity,
    Zhanhd\Object\Player\Entity  as PlayerEntity,
    Zhanhd\ReqRes\Marry\Request  as MarryRequest,
    Zhanhd\ReqRes\Marry\Response as MarryResponse;

/**
 *
 */
$RING_ID = 250101;

/**
 *
 */
return function (Client $c) use ($RING_ID) {
    if (-1 === ($request = $this->parseParameters($c, new MarryRequest))) {
        return;
    }

    $p = $c->local->player;
    
    /**
     * @check
     */
    if ($p->counterCycle->marry >= 3) {
        return $c->addReply($this->errorResponse->error('daily times of marry reach the maximum'));
    }

    if ($p->profile->$RING_ID < 1) {
        return $c->addReply($this->errorResponse->error('has no ring'));
    }

    $pe = new PlayerEntity;
    if (false === $pe->findByPid($request->peid->intval(), $p->id)) {
        return $c->addReply($this->errorResponse->error('notfound hero'));
    }
    
    if ($pe->e->rarity < 1) {
        return $c->addReply($this->errorResponse->error('rarity not enough'));
    }

    if ($pe->property->married) {
        return $c->addReply($this->errorResponse->error('has married'));
    }

    if ($pe->property->love < 100) {
        return $c->addReply($this->errorResponse->error('notenough love value'));
    }

    
        $pe->property->married = 1;
        $pe->save();
        
        $p->profile->$RING_ID --;
        $p->profile->save();
        
        $p->counterCycle->marry ++;
        $p->counterCycle->save();
        
        $r = new MarryResponse;
        $r->wife->intval($pe->id);
        $r->ring->intval($p->profile->$RING_ID);
        $c->addReply($r);
};

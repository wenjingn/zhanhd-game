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
use Zhanhd\ReqRes\Hero\Transfer\Request,
    Zhanhd\ReqRes\Hero\Transfer\Response,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\Config\Store,
    Zhanhd\Config\Entity        as SourceEntity,
    Zhanhd\Object\Player\Entity as PlayerEntity,
    Zhanhd\Extension\Consume\Module as ConsumeModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $pe = new PlayerEntity;
    if (false === $pe->findByPid($request->peid->intval(), $p->id)) {
        return $c->addReply($this->errorResponse->error('hero not found'));
    }
    
    if ($pe->e->type <> SourceEntity::TYPE_HERO) {
        return $c->addReply($this->errorResponse->error('invalid type'));
    }

    $armies     = $pe->e->army;
    $nextArmyId = false;
    foreach ($armies as $aid => $unlock) {
        if ($nextArmyId) {
            $nextArmyId = $aid;
            break;
        }

        if ($aid == $pe->property->aid) {
            $nextArmyId = true;
        }
    }
    if (is_bool($nextArmyId)) {
        return $c->addReply($this->errorResponse->error('cannot transfer'));
    }

    if ($pe->lvl < $unlock) {
        return $c->addReply($this->errorResponse->error('hero level not enough'));
    }
    
    $army = Store::get('army', $nextArmyId);
    foreach ($army->upgradations as $eid => $num) {
        if ($p->profile->$eid < $num) {
            return $c->addReply($this->errorResponse->error('resource not enough'));
        }
    }

    ConsumeModule::aspect($c, $army->upgradations);

    $pe->property->aid = $nextArmyId;
    $p->profile->save();
    $pe->save();
    $r = new Response;
    $r->peid->intval($pe->id);
    $r->aid ->intval($pe->property->aid);
    $c->addReply($r);
};

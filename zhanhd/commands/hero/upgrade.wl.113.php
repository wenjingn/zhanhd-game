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
use Zhanhd\ReqRes\Hero\Upgrade\Request,
    Zhanhd\ReqRes\Hero\Upgrade\Response,
    Zhanhd\Config\HeroExp,
    Zhanhd\Config\Entity        as SourceEntity,
    Zhanhd\Object\Player\Entity as PlayerEntity;

define('EXPCARDID', 410242);
define('EXPCARDEXP', 1000);

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;

    $expcardNum = $request->expcardNum->intval();
    if ($expcardNum === 0 && $request->eliminatings->size() === 0) {
        return $c->addReply($this->errorResponse->error('upgrade provide nothing'));
    }

    if ($p->profile->{EXPCARDID} < $expcardNum) {
        return $c->addReply($this->errorResponse->error('notenough expcard'));
    }

    $pe = new PlayerEntity;
    if (false === $pe->findByPid($request->peid->intval(), $p->id)) {
        return $c->addReply($this->errorResponse->error('hero not found'));
    }

    if ($pe->e->type != SourceEntity::TYPE_HERO) {
        return $c->addReply($this->errorResponse->error('pe must be hero'));
    }

    if ($pe->lvl == HeroExp::MAX_LEVEL) {
        return $c->addReply($this->errorResponse->error('already max-level'));
    }

    for ($expcardUsed = 0; $expcardUsed < $expcardNum; $expcardUsed++) {
        if ($pe->lvl == HeroExp::MAX_LEVEL) {
            goto response;
        }
        $pe->addexp(EXPCARDEXP);
    }
    
    $eliminatings = [];
    foreach ($request->eliminatings as $i => $o) {
        $id = $o->intval();
        if ($id == $pe->id) {
            return $c->addReply($this->errorResponse->error('can not consume itself'));
        }

        if (isset($eliminatings[$id])) {
            return $c->addReply($this->errorResponse->error('provide duplicate pe'));
        }

        $eliminating = new PlayerEntity;
        if (false === $eliminating->findByPid($id, $p->id)) {
            return $c->addReply($this->errorResponse->error('hero not found'));
        }

        if ($eliminating->flags == PlayerEntity::FLAG_INUSE) {
            return $c->addReply($this->errorResponse->error('pe already inuse'));
        }

        $pe->addexp($eliminating->getexp());
        $eliminatings[$id] = $eliminating;

        if ($pe->lvl == HeroExp::MAX_LEVEL) {
            break;
        }
    }

    foreach ($eliminatings as $o) {
        $o->drop();
    }

response:
    $p->profile->{EXPCARDID} -= $expcardNum;
    $p->profile->save();
    $pe->save();

    $r = new Response;
    $r->peid->intval($pe->id);
    $r->exp->intval($pe->exp);
    $r->lvl->intval($pe->lvl);
    $r->expcardNum->intval($expcardUsed);
    $r->eliminated->resize(count($eliminatings));
    $eliminatings = array_values($eliminatings);
    foreach ($r->eliminated as $i => $o) {
        $o->intval($eliminatings[$i]->id);
    }
    $c->addReply($r);
};

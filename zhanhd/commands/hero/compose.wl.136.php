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
use Zhanhd\ReqRes\Hero\Compose\Request,
    Zhanhd\ReqRes\Hero\Compose\Response,
    Zhanhd\ReqRes\SysMsgResponse,
    Zhanhd\Config\Store,
    Zhanhd\Config\Entity as SourceEntity,
    Zhanhd\Extension\Reward\Module as RewardModule,
    Zhanhd\Extension\Guide         as GuideModule;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    if (null === ($e = Store::get('entity', $request->eid->intval())) || $e->type != SourceEntity::TYPE_SOUL) {
        return $c->addReply($this->errorResponse->error('notfound soul'));
    }

    if ($p->profile->{$e->id} < 100) {
        return $c->addReply($this->errorResponse->error('notenough soul'));
    }

    if (null === ($h = Store::get('entity', (int)($e->id/10)))) {
        return $c->addReply($this->errorResponse->error('notfound hero'));
    }

    $p->profile->{$e->id} -= 100;
    $p->profile->save();
    $increase = (new Object)->import([
        $h->id => [
            'e' => $h,
            'n' => 1,
        ],
    ]);

    /* broadcast */
    if ($h->rarity == 4) {
        $smr = new SysMsgResponse;
        $smr->format(1, 1, array($p->name, '战魂招募', array($h->tag, 'orange')));
        $this->task('broadcast-global', $smr);
    } else if ($h->rarity == 5) {
        $smr = new SysMsgResponse;
        $smr->format(1, 2, array($p->name, '战魂招募', array($h->tag, 'red')));
        $this->task('broadcast-global', $smr);
    }

    $r = new Response;
    $r->eid->intval($e->id);
    $r->num->intval($p->profile->{$e->id});
    $p->increaseEntity($increase, function($pe, $hero, $global) {
        $hero->fromPlayerEntityObject($pe, $this);
    }, $r->hero, $this);
    $c->addReply($r);
};

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
use Zhanhd\ReqRes\Fragment\Request,
    Zhanhd\ReqRes\Fragment\Response,
    Zhanhd\Config\Store,
    Zhanhd\Config\Entity;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }
    
    $p = $c->local->player;
    if (null === ($frag = Store::get('entity', $request->frag->intval()))) {
        return $c->addReply($this->errorResponse->error('notfound prop'));
    }

    if ($frag->type != Entity::TYPE_FRAGMENT) {
        return $c->addReply($this->errorResponse->error('invalid entity type'));
    }

    foreach($frag->property as $cohesion => $require);
    $num = $request->num->intval();
    $require *= $num;
    if ($p->profile->{$frag->id} < $require) {
        return $c->addReply($this->errorResponse->error('notenough resource'));
    }

    $p->profile->{$frag->id} -= $require;
    $p->profile->{$cohesion} += $num;
    $p->profile->save();

    $r = new Response;
    $r->frag->intval($frag->id);
    $r->cost->intval($require);
    $r->eid->intval($cohesion);
    $r->incr->intval($num);
    $c->addReply($r);
};

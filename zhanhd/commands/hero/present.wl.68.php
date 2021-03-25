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
use Zhanhd\ReqRes\Hero\Present\Request,
    Zhanhd\ReqRes\Hero\Present\Response,
    Zhanhd\Config\Store,
    Zhanhd\Config\Entity,
    Zhanhd\Object\Player\Entity as PlayerEntity;

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
        return $c->addReply($this->errorResponse->error('notfound hero'));
    }

    if ($pe->e->type != Entity::TYPE_HERO) {
        return $c->addReply($this->errorResponse->error('invalid pe type'));
    }

    if ($pe->property->married) {
        return $c->addReply($this->errorResponse->error('has married'));
    }

    $lovevalue = $pe->property->love;
    $propused = [];
    foreach ($request->props as $prop) {
        if (null === ($e = Store::get('entity', $prop->eid->intval()))) {
            break;
        }

        if ($e->type != Entity::TYPE_GIFT) {
            break;
        }

        $num = min($prop->num->intval(), $p->profile->{$e->id});
        $num = min($num, ceil((100-$lovevalue)/$e->property->love));
        $propused[$e->id] = $num;
        $lovevalue += $e->property->love*$num;
        if ($lovevalue >= 100) {
            break;
        }
    }

    $r = new Response;
    $r->peid->intval($pe->id);
    $pe->property->love = min($lovevalue, 100);
    $pe->save();
    $r->love->intval($pe->property->love);

    $r->props->resize(count($propused));
    $i = 0;
    foreach ($propused as $eid => $num) {
        $p->profile->{$eid} -= $num;
        $r->props->get($i)->eid->intval($eid);
        $r->props->get($i)->num->intval($p->profile->{$eid});
        $i++;
    }
    $p->profile->save();
    $c->addReply($r);
};

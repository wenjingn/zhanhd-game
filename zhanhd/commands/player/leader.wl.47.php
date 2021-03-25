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
    Zhanhd\ReqRes\LeaderInfo            as Request,
    Zhanhd\ReqRes\Player\LeaderResponse,
    Zhanhd\Object\Player,
    Zhanhd\Extension\BadwordFilter;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;

    // player-name
    if ($request->name->strlen()) {
        if (($name = $request->name->strval()) <> $p->name) {
            $filter = new BadwordFilter;
            if (false === $filter->check($name)) {
                return $c->addReply($this->errorResponse->error('nickname contains badword'));
            }

            $p = new Player;
            if (($pid = $p->nameExists($c->zone->intval(), $name)) && $pid <> $p->id) {
                return $c->addReply($this->errorResponse->error('player-name already exists'));
            }

            $p->name = $name;
        }
    }

    // player-sex
    $sex = $request->sex->intval();
    if (false === Store::has('leader', $sex)) {
        return $c->addReply($this->errorResponse->error('invalid sex'));
    }

    $p->profile->sex = $sex;

    // player-img
    foreach (Store::get('leader', $sex) as $part => $maps) {
        $v = $request->img->$part->intval();
        if (false === isset($maps[$v])) {
            return $c->addReply($this->errorResponse->error('invalid img'));
        }

        if ($maps[$v] && false === $p->isMember()) {
            return $c->addReply($this->errorResponse->error('deny exclusive vip'));
        }

        $p->profile->$part = $v;
    }

    // saving player
    $p->save();

    // send response
    $r = new LeaderResponse;
    $r->leader->fromPlayerObject($p);
    $c->addReply($r);
};

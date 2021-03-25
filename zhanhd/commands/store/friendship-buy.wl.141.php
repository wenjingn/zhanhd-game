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
use Zhanhd\ReqRes\Store\FriendShip\Request,
    Zhanhd\ReqRes\Store\FriendShip\Response,
    Zhanhd\ReqRes\Store\FriendShipStore as FriendShipStoreResponse,
    Zhanhd\ReqRes\Relation\FriendShipUpdateResponse,
    Zhanhd\Extension\Reward\Module as RewardModule,
    Zhanhd\Extension\FriendShipStore\Module as FriendShipStoreModule,
    Zhanhd\Object\Player\Coherence as PlayerCoherence,
    Zhanhd\Config\Store;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $version = $request->version->intval();
    $gid = $request->gid->intval();

    if (false === Store::has('friendShipGoods', $gid)) {
        return $c->addReply($this->errorResponse->error('notfound friendship-goods'));
    }

    if ($version != $this->date) {
        $c->addReply($this->errorResponse->error('expire friendship-goods'));
        $r = new FriendShipStoreResponse;
        $r->friendShipStore->fromGlobalObject($this);
        return $c->addReply($r);
    }

    $goods = FriendShipStoreModule::fetch($this->redis, $this->date);
    if (false === in_array($gid, $goods)) {
        return $c->addReply($this->errorResponse->error('notfound friendship-goods'));
    }

    $goods = Store::get('friendShipGoods', $gid);

    if (false === PlayerCoherence::decrease($this->pdo, $p->id, 'friendship', $goods->price)) {
        return $c->addReply($this->errorResponse->error('notenough friendship'));
    }

    $r = new FriendShipUpdateResponse;
    $r->flag->intval(1);
    $r->value->intval($goods->price);
    $c->addReply($r);

    $r = new Response;
    RewardModule::aspect($p, $goods->getRewards(), $r->rewards, $c, $this);
    $c->addReply($r);
};

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
use Zhanhd\ReqRes\Guild\Gift\Request,
    Zhanhd\ReqRes\Guild\Gift\Response,
    Zhanhd\Config\Store,
    Zhanhd\Extension\Reward\Module as RewardModule;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    if (null === ($gift = Store::get('guildGift', $request->gift->intval()))) {
        return $c->addReply($this->errorResponse->error('notfound guild-gift'));
    }
    
    $p = $c->local->player;
    $guildMember = $p->getGuildMember();
    if (null === $guildMember) {
        return $c->addReply($this->errorResponse->error('denied privilege guild'));
    }

    $guild = $guildMember->getGuild();
    if ($guild->lvl < $gift->lvl) {
        return $c->addReply($this->errorResponse->error('limit guild-gift guild-lvl'));
    }

    $bit = 1 << ($gift->lvl-1);
    if ($bit & $p->profile->guildGiftAccepted) {
        return $c->addReply($this->errorResponse->error('repeat accept guild-gift'));
    }

    $p->profile->guildGiftAccepted |= $bit;
    $p->profile->save();
    $r = new Response;
    $r->gift->intval($gift->id);
    RewardModule::aspect($p, $gift->source, $r->rewards, $c, $this);
    $c->addReply($r);
};

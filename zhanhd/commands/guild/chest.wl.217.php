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
use Zhanhd\ReqRes\Guild\Chest\Response,
    Zhanhd\Config\Store,
    Zhanhd\Extension\Reward\Module as RewardModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === $this->parseParametersNone($c)) {
        return;
    }

    $p = $c->local->player;
    $guildMember = $p->getGuildMember();
    if (null === $guildMember) {
        return $c->addReply($this->errorResponse->error('denied privilege guild'));
    }

    if ($this->ustime - $guildMember->join < 7*86400*1000000) {
        return $c->addReply($this->errorResponse->error('denied privilege guild jointime'));
    }

    if ($p->counterWeekly->guildChest) {
        return $c->addReply($this->errorResponse->error('repeat accepte guild-chest'));
    }

    $guild = $guildMember->getGuild();
    $chest = Store::get('guildChest', $guild->lvl);
    if ($guildMember->cont - $guildMember->contused < $chest->score) {
        return $c->addReply($this->errorResponse->error('notenough contribution'));
    }

    $guildMember->contused += $chest->score;
    $guildMember->save();
    $p->counterWeekly->guildChest = 1;
    $p->counterWeekly->save();
    $r = new Response;
    $r->score->intval($guildMember->cont - $guildMember->contused);
    RewardModule::aspect($p, $chest->source, $r->rewards, $c, $this);
    $c->addReply($r);
};

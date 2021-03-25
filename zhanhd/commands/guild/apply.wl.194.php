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
use Zhanhd\ReqRes\Guild\Apply\Request,
    Zhanhd\ReqRes\Guild\Apply\Notify,
    Zhanhd\Object\Guild,
    Zhanhd\Object\Guild\Member  as GuildMember,
    Zhanhd\Object\Guild\Pending as GuildPending;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    if ($p->getGuildMember()) {
        return $c->addReply($this->errorResponse->error('limit guild join'));
    }

    if ($p->recent->getCdJoinGuild($this->ustime)) {
        return $c->addReply($this->errorResponse->error('cd guild join'));
    }

    $guild = new Guild;
    if (false === $guild->find($request->gid->intval())) {
        return $c->addReply($this->errorResponse->error('notfound guild'));
    }
    
    if (null === ($pending = $guild->addPending($p))) {
        return $c->addReply($this->errorResponse->error('repeat guild join'));
    }
    $pending->convertMember($p);
    $r = new Notify;
    $r->applies->resize(1);
    $r->applies->get(0)->fromGuildMemberObject($pending);
    $this->sendTo($guild->getPresident()->pid, $r);
    if ($viceChairman = $guild->getViceChairman()) {
        $this->sendTo($viceChairman->pid, $r);
    }
};

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
use Zhanhd\ReqRes\Guild\Create\Request,
    Zhanhd\ReqRes\Guild\Create\Response,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\Object\Guild,
    Zhanhd\Object\Guild\Member,
    Zhanhd\Object\Guild\Pending,
    Zhanhd\Task\UpdateGuildRank,
    Zhanhd\Extension\BadwordFilter;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $name = $request->name->strval();
    if (empty($name)) {
        return $c->addReply($this->errorResponse->error('empty guild-name'));
    }

    if (mb_strlen($name) > 24) {
        return $c->addReply($this->errorResponse->error('high-limit length guild-name'));
    }

    $filter = new BadwordFilter;
    if (false === $filter->check($name)) {
        return $c->addReply($this->errorResponse->error('badword'));
    }

    if ($p->getGuildMember()) {
        return $c->addReply($this->errorResponse->error('limit guild join'));
    }

    if ($p->recent->getCdJoinGuild($this->ustime)) {
        return $c->addReply($this->errorResponse->error('cd guild create'));
    }

    $guild = new Guild;
    if ($guild->findByName($name)) {
        return $c->addReply($this->errorResponse->error('exists guild-name'));
    }

    if ($p->gold < Guild::PRICE) {
        return $c->addReply($this->errorResponse->error('notenough diamond'));
    }

    $p->decrGold(Guild::PRICE);
    $p->save();
    $guild->name = $name;
    $guild->lvl = 1;
    $guild->founder = $p->id;
    $guild->save();
    $president = $guild->addMember($p, Member::POST_PRESIDENT);
    $this->redis->hset(sprintf('zhanhd:ht:guildmembers:%d', $president->gid), $president->pid, $c->sock->intval());

    $pendings = Pending::getsByPid($this->pdo, $p->id);
    foreach ($pendings as $pending) {
        $g = new Guild;
        $g->find($pending->gid);
        $g->removePending($pending);
    }

    $r = new ResourceResponse;
    $r->retval->fromOwnerObject($p);
    $c->addReply($r);
    $r = new Response;
    $r->guild->fromObject($guild, $president, $this);
    $c->addReply($r);

    $x = new UpdateGuildRank;
    $x->gid->intval($guild->id);
    $this->task('guild-rank', $x);
};

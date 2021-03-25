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
use Zhanhd\ReqRes\Guild\Manage\Expel\Request,
    Zhanhd\ReqRes\Guild\Manage\Expel\Response,
    Zhanhd\Task\Guild\Request as TaskGuildRequest,
    Zhanhd\Task\UpdateGuildRank;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $guildManager = $p->getGuildMember();
    if (null === $guildManager || false === $guildManager->isGuildManager()) {
        return $c->addReply($this->errorResponse->error('denied privilege guild'));
    }

    $guild = $guildManager->getGuild();
    $guildMember = $guild->getMember($request->pid->intval());
    if ($guildMember === null) {
        return $c->addReply($this->errorResponse->error('notfound guild member'));
    }

    $guild->removeMember($guildMember);
    $r = new Response;
    $r->pid->intval($guildMember->pid);
    $taskReq = new TaskGuildRequest;
    $taskReq->setup($guild->id, $r);
    $this->task('broadcast-guild', $taskReq);
    $this->redis->hdel(sprintf('zhanhd:ht:guildmembers:%d', $guildMember->gid), $guildMember->pid);

    $x = new UpdateGuildRank;
    $x->gid->intval($guild->id);
    $this->task('guild-rank', $x);
};

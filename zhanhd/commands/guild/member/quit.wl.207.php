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
use Zhanhd\ReqRes\Guild\Member\Quit\Response,
    Zhanhd\Task\Guild\Request as TaskGuildRequest,
    Zhanhd\Task\UpdateGuildRank;

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

    if ($guildMember->isPresident()) {
        return $c->addReply($this->errorResponse->error('deprecated president quit'));
    }

    $guild = $guildMember->getGuild();
    $guild->removeMember($guildMember);
    $this->redis->hdel(sprintf('zhanhd:ht:guildmembers:%d', $guildMember->gid), $guildMember->pid);

    $r = new Response;
    $r->pid->intval($guildMember->pid);
    $c->addReply($r);
    $taskReq = new TaskGuildRequest;
    $taskReq->setup($guildMember->gid, $r);
    $this->task('broadcast-guild', $taskReq);

    $x = new UpdateGuildRank;
    $x->gid->intval($guild->id);
    $this->task('guild-rank', $x);
};

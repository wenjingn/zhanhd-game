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
use Zhanhd\ReqRes\Guild\Manage\Transfer\Request,
    Zhanhd\ReqRes\Guild\Manage\Transfer\Response,
    Zhanhd\Object\Guild\Member as GuildMember,
    Zhanhd\Task\Guild\Request  as TaskGuildRequest,
    Zhanhd\Object\Message;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $president = $p->getGuildMember();
    if (null === $president || false === $president->isPresident()) {
        return $c->addReply($this->errorResponse->error('denied privilege guild'));
    }

    if ($request->pid->intval() == $president->pid) {
        return $c->addReply($this->errorResponse->error('meaningless'));
    }

    $guild = $president->getGuild();
    $guildMember = $guild->getMember($request->pid->intval());
    if (null === $guildMember) {
        return $c->addReply($this->errorResponse->error('notfound guild member'));
    }

    $president->post = GuildMember::POST_OLDDRIVER;
    $president->save();
    $guildMember->post = GuildMember::POST_PRESIDENT;
    $guildMember->save();

    $r = new Response;
    $r->pid->intval($guildMember->pid);
    $r->leader->fromPlayerObject($guildMember->player);

    $m = new Message;
    $m->pid = $guildMember->pid;
    $m->tag = Message::TAG_GUILD_TRANSFER;
    $m->addArgv($guild->name);
    $m->save();

    $taskReq = new TaskGuildRequest;
    $taskReq->setup($guild->id, $r);
    $this->task('broadcast-guild', $taskReq);
};

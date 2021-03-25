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
use Zhanhd\ReqRes\Guild\Manage\Appoint\Request,
    Zhanhd\ReqRes\Guild\Manage\Appoint\Response,
    Zhanhd\Object\Guild\Member as GuildMember,
    Zhanhd\Task\Guild\Request as TaskGuildRequest,
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
    if ($president === null || false === $president->isPresident()) {
        return $c->addReply($this->errorResponse->error('denied privilege guild'));
    }

    if ($request->pid->intval() == $president->pid) {
        return $c->addReply($this->errorResponse->error('deprecated appoint president'));
    }

    $guild = $president->getGuild();
    $guildMember = $guild->getMember($request->pid->intval());
    if (null === $guildMember) {
        return $c->addReply($this->errorResponse->error('notfound guild member'));
    }
    if ($guildMember->isViceChairman()) {
        return $c->addReply($this->errorResponse->error('meaningless'));
    }

    $viceChairman = $guild->getViceChairman();
    if ($viceChairman) {
        $viceChairman->post = GuildMember::POST_GREENER;
        $viceChairman->getPost();
        $viceChairman->save();
    }
    $guildMember->post = GuildMember::POST_VICECHAIRMAN;
    $guildMember->save();

    $m = new Message;
    $m->pid = $guildMember->pid;
    $m->tag = Message::TAG_GUILD_APPOINT;
    $m->addArgv($guild->name);
    $m->save();
    
    $r = new Response;
    $r->pid->intval($guildMember->pid);
    $r->viceChairmanName->strval($guildMember->player->name);
    
    $taskReq = new TaskGuildRequest;
    $taskReq->setup($guild->id, $r);
    $this->task('broadcast-guild', $taskReq);
};

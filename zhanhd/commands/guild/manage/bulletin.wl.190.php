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
use Zhanhd\ReqRes\Guild\Manage\Bulletin\Request,
    Zhanhd\ReqRes\Guild\Manage\Bulletin\Response,
    Zhanhd\Task\Guild\Request as TaskGuildRequest,
    Zhanhd\Extension\BadwordFilter;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $guildManager = $p->getGuildMember();
    if ($guildManager === null || $guildManager->isGuildManager() === false) {
        return $c->addReply($this->errorResponse->error('denied privilege guild'));
    }

    $bulletin = $request->bulletin->strval();
    if (strlen($bulletin) > 128) {
        return $c->addReply($this->errorResponse->error('high-limit guild-bulletin length'));
    }

    $filter = new BadwordFilter;
    if (false === $filter->check($bulletin)) {
        return $c->addReply($this->errorResponse->error('badword'));
    }

    $guild = $guildManager->getGuild();
    $guild->bulletin = $bulletin;
    $guild->save();
    
    $r = new Response;
    $r->bulletin->strval($guild->bulletin);
    $taskReq = new TaskGuildRequest;
    $taskReq->setup($guild->id, $r);
    $this->task('broadcast-guild', $taskReq);
};

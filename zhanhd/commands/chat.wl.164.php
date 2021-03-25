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
use Zhanhd\ReqRes\Chat\Request,
    Zhanhd\ReqRes\Chat\Response,
    Zhanhd\Task\Guild\Request as TaskGuildRequest,
    Zhanhd\Extension\BadwordFilter;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;

    $channel = $request->channel->intval();

    $filter = new BadwordFilter;
    if (false === $filter->check($request->content->strval())) {
        return $c->addReply($this->errorResponse->error('badword'));
    }

    $r = new Response;
    $r->chat->fromPlayerObject($p, $request, $this);
    switch ($channel) {
    case 1:
        $this->task('broadcast-global', $r);
        break;
    case 2:
        $guildMember = $p->getGuildMember();
        if (null === $guildMember) {
            return $c->addReply($this->errorResponse->error('denied not guild member'));
        }
        $taskReq = new TaskGuildRequest;
        $taskReq->setup($guildMember->gid, $r);
        $this->task('broadcast-guild', $taskReq);
        break;
    case 3:
        $to = $request->to->intval();
        $r->to->intval($p->id);
        $this->sendTo($to, $r);
        $r = new Response;
        $r->chat->fromPlayerObject($p, $request, $this);
        $r->to->intval($to);
        $c->addReply($r);
        break;
    }
};

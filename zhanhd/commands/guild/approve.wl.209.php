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
use Zhanhd\ReqRes\Guild\Pending\Approve\Request,
    Zhanhd\ReqRes\Guild\Pending\Approve\Response,
    Zhanhd\ReqRes\Guild\Pending\Approve\Notify,
    Zhanhd\Object\Player,
    Zhanhd\Object\Guild,
    Zhanhd\Object\Guild\Member as GuildMember,
    Zhanhd\Object\Guild\Pending as GuildPending,
    Zhanhd\Task\UpdateGuildRank,
    Zhanhd\Task\Guild\Request as TaskGuildRequest,
    Zhanhd\Object\Message;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    if ($request->pids->size() === 0) {
        return $c->addReply($this->errorResponse->error('empty params'));
    }

    $p = $c->local->player;
    $guildManager = $p->getGuildMember();
    if (null === $guildManager || false === $guildManager->isGuildManager()) {
        return $c->addReply($this->errorResponse->error('denied privilege guild'));
    }

    $guild = $guildManager->getGuild();
    $pendings = $guild->getPendings()->map(null, function($k, $v) {
        return $v->pid;
    });

    $memnum = $guild->memnum;
    $approved = [];
    foreach ($request->pids as $o) {
        if ($memnum >= $guild->getMemberLimit()) {
            $c->addReply($this->errorResponse->error('high-limit guild-member'));
            break;
        }

        $pid = $o->intval();
        if (false === isset($pendings->$pid)) {
            $c->addReply($this->errorResponse->error('invalid guild pending pid'));
            continue;
        }

        $guildMember = new GuildMember;
        if ($guildMember->findByPid($pid)) {
            $c->addReply($this->errorResponse->error('guild-member has joined guild'));
            continue;
        }

        $approved[] = $pendings->$pid;
        $memnum++;
    }

    $guildSet = [];
    foreach ($approved as $pending) {
        $memberPendings = GuildPending::getsByPid($this->pdo, $pending->pid);
        foreach ($memberPendings as $memberPending) {
            if (false === isset($guildSet[$memberPending->gid])) {
                $guildSet[$memberPending->gid] = new Guild;
                $guildSet[$memberPending->gid]->find($memberPending->gid);
            }
            $guildSet[$memberPending->gid]->removePending($memberPending);
        }
    }

    $r = new Response;
    $r->pids->resize(count($approved));
    foreach ($approved as $i => $o) {
        $player = new Player;
        $player->find($o->pid);
        $newGuildMember = $guild->addMember($player, GuildMember::POST_GREENER);
        if ($fd = $this->redis->hget('zhanhd:ht:onlines', $newGuildMember->pid)) {
            $this->redis->hset(sprintf('zhanhd:ht:guildmembers:%d', $newGuildMember->gid), $newGuildMember->pid, $fd);
        }

        $m = new Message;
        $m->pid = $newGuildMember->pid;
        $m->tag = Message::TAG_GUILD_JOIN;
        $m->addArgv($guild->name);
        $m->save();

        $notify = new Notify;
        $notify->guild->fromObject($guild, $newGuildMember, $this);
        $this->sendTo($o->pid, $notify);
        $r->pids->get($i)->intval($o->pid);
    }

    $taskReq = new TaskGuildRequest;
    $taskReq->setup($guild->id, $r);
    $this->task('broadcast-guild', $taskReq);

    $x = new UpdateGuildRank;
    $x->gid->intval($guild->id);
    $this->task('guild-rank', $x);
};

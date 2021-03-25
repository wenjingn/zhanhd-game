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
use Zhanhd\ReqRes\Guild\Impeach\Response,
    Zhanhd\ReqRes\Guild\Impeach\NewPresidentNotify,
    Zhanhd\Task\Guild\Request as TaskGuildRequest,
    Zhanhd\Object\Guild,
    Zhanhd\Object\Guild\Member as GuildMember,
    Zhanhd\Object\Guild\Impeach as GuildImpeach,
    Zhanhd\Object\Message;

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
    $post = $guildMember->getPost();
    if ($post == GuildMember::POST_GREENER) {
        return $c->addReply($this->errorResponse->error('denied privilege guild'));
    }
    if ($post == GuildMember::POST_PRESIDENT) {
        return $c->addReply($this->errorResponse->error('meaningless'));
    }

    $guild = $guildMember->getGuild();
    $president = $guild->getPresident();
    if ($president->player->logout == 0 || $this->ustime-$president->player->logout < 3*86400*1000000) {
        $r = new Response;
        $taskReq = new TaskGuildRequest;
        $taskReq->setup($guild->id, $r);
        $this->task('broadcast-guild', $taskReq);
        return $c->addReply($this->errorResponse->error('deprecated impeach'));
    }

    $guildImpeach = $guild->getImpeach();
    if ($guildImpeach && isset($guildImpeach->members->{$guildMember->pid})) {
        return $c->addReply($this->errorResponse->error('deprecated impeach more'));
    }
    if ($guildImpeach === null) {
        $guildImpeach = new GuildImpeach;
        $guildImpeach->gid = $guild->id;
        $guildImpeach->time = $this->ustime;
        $guildImpeach->save();

        $m = new Message;
        $m->gid = $guild->id;
        $m->tag = Message::TAG_GUILD_IMPEACHSTART;
        $m->save();
    }

    $guildImpeach->addMember($guildMember);
    $impeachNum = $guildImpeach->members->count();
    if ($impeachNum >= Guild::IMPEACHNUM) {
        $guildImpeach->drop();
        $newPresident = $guild->selectNewPresident();
        if (null === $newPresident) {
            $notify = new NewPresidentNotify;
            $notify->pid->intval($president->pid);
            $notify->leader->fromPlayerObject($president->player);
            $taskReq = new TaskGuildRequest;
            $taskReq->setup($guild->id, $notify);
            $this->task('broadcast-guild', $taskReq);
        } else {
            $president->post = GuildMember::POST_OLDDRIVER;
            $president->save();
            $newPresident->post = GuildMember::POST_PRESIDENT;
            $newPresident->save();
            
            $notify = new NewPresidentNotify;
            $notify->pid->intval($newPresident->pid);
            $notify->leader->fromPlayerObject($newPresident->player);
            $taskReq = new TaskGuildRequest;
            $taskReq->setup($guild->id, $notify);
            $this->task('broadcast-guild', $taskReq);

            $m = new Message;
            $m->gid = $guild->id;
            $m->tag = Message::TAG_GUILD_IMPEACHSUCC;
            $m->addArgv($newPresident->player->name);
            $m->save();
        }
    } else {
        $r = new Response;
        $r->impeachNum->intval($impeachNum);
        $r->CONFIG_IMPEACHNUM->intval(Guild::IMPEACHNUM);
        $taskReq = new TaskGuildRequest;
        $taskReq->setup($guild->id, $r);
        $this->task('broadcast-guild', $taskReq);
    }
};

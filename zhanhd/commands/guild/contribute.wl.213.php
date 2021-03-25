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
use Zhanhd\ReqRes\Guild\Contribute\Request,
    Zhanhd\ReqRes\Guild\Contribute\Response,
    Zhanhd\ReqRes\Guild\Contribute\Notify,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\ReqRes\Relation\FriendShipUpdateResponse,
    Zhanhd\ReqRes\Guild\ExpNotify,
    Zhanhd\Task\Guild\Request as TaskGuildRequest,
    Zhanhd\Config\Store,
    Zhanhd\Config\Entity,
    Zhanhd\Object\Player\Coherence,
    Zhanhd\Task\UpdateGuildRank;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    if (null === ($contribution = Store::get('guildContribution', $request->cid->intval()))) {
        return $c->addReply($this->errorResponse->error('notfound guild-contribution'));
    }

    $p = $c->local->player;
    $guildMember = $p->getGuildMember();
    if ($guildMember === null) {
        return $c->addReply($this->errorResponse->error('denied privilege guild'));
    }

    if ($guildMember->daily->contribution) {
        return $c->addReply($this->errorResponse->error('daily-limit guild contribute'));
    }

    $e = Store::get('entity', $contribution->eid);
    if ($e->type == Entity::TYPE_RESOURCE) {
        if ($p->profile->{$e->id} < $contribution->num) {
            return $c->addReply($this->errorResponse->error('notenough resource'));
        }
        $p->profile->{$e->id} -= $contribution->num;
    } else if ($e->type == Entity::TYPE_MONEY) {
        if ($p->gold < $contribution->num) {
            return $c->addReply($this->errorResponse->error('notenough diamond'));
        }
        $p->decrGold($contribution->num);
    } else {
        return $c->addReply($this->errorResponse->error('invalid contribution'));
    }

    $p->save();
    $guildMember->daily->contribution += $contribution->friendship;
    $guildMember->cont += $contribution->friendship;
    $guildMember->save();
    Coherence::increase($this->pdo, $p->id, 'friendship', $contribution->friendship);

    $guild = $guildMember->getGuild();
    $level = $guild->lvl;
    $guild->contribute($contribution->contribution);

    $r = new Response;
    $r->cid->intval($contribution->id);
    $r->contribution->intval($contribution->contribution);
    $c->addReply($r);

    $r = new ResourceResponse;
    $r->retval->fromOwnerObject($p);
    $c->addReply($r);

    $r = new FriendShipUpdateResponse;
    $r->flag->intval(0);
    $r->value->intval($contribution->friendship);
    $c->addReply($r);

    $r = new ExpNotify;
    $r->expinfo->exp->intval($guild->getCurrExp());
    $r->expinfo->lvl->intval($guild->lvl);
    $taskReq = new TaskGuildRequest;
    $taskReq->setup($guild->id, $r);
    $this->task('broadcast-guild', $taskReq);

    $r = new Notify;
    $r->nickname->strval($p->name);
    $r->contId->intval($contribution->id);
    $taskReq = new TaskGuildRequest;
    $taskReq->setup($guild->id, $r);
    $this->task('broadcast-guild', $taskReq);

    if ($guild->lvl > $level) {
        $x = new UpdateGuildRank;
        $x->gid->intval($guild->id);
        $this->task('guild-rank', $x);
    }
};

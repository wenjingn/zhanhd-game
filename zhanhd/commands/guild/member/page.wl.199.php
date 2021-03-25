<?php
/**
 * $Id$
 */

/**
 *
 */
use Zhanhd\ReqRes\Guild\Member\Page\Request,
    Zhanhd\ReqRes\Guild\Member\Page\Response,
    Zhanhd\Object\Guild\Member as GuildMember;

/**
 *
 */
use System\Swoole\Client;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $guildMember = $p->getGuildMember();
    if (null === $guildMember) {
        return $c->addReply($this->errorResponse->error('denied privilege guild'));
    }

    $page = GuildMember::getPage($this->pdo, $guildMember->gid, $request->index->intval());
    $r = new Response;
    $r->index->intval($page->index);
    $r->total->intval($page->total);
    $r->members->resize($page->data->count());
    foreach ($r->members as $i => $o) {
        $o->fromGuildMemberObject($page->data->get($i));
    }
    $c->addReply($r);
};

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
use Zhanhd\ReqRes\Guild\Search\Request,
    Zhanhd\ReqRes\Guild\Search\Response,
    Zhanhd\Object\Guild;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $keyword = $request->keyword->strval();
    if (mb_strlen($keyword) > 24) {
        return $c->addReply($this->errorResponse->error('high-limit keyword-length'));
    }

    $pending = $p->getGuildPending();
    $filter = [];
    foreach ($pending as $o) {
        $filter[] = $o->gid;
    }
    if (empty($keyword)) {
        $guilds = Guild::recommendGuild($this->pdo, $filter);
    } else {
        $guilds = Guild::keywordSearch($this->pdo, $keyword, $filter);
    }

    $r = new Response;
    $r->guilds->resize($guilds->count());
    foreach ($r->guilds as $i => $o) {
        $o->fromGuildObject($guilds->get($i));
    }
    $c->addReply($r);
};

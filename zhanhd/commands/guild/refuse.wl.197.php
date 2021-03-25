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
use Zhanhd\ReqRes\Guild\Pending\Refuse\Request,
    Zhanhd\ReqRes\Guild\Pending\Refuse\Response;

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
    $guildMember = $p->getGuildMember();
    if (null === $guildMember || false === $guildMember->isGuildManager()) {
        return $c->addReply($this->errorResponse->error('denied privilege guild'));
    }

    $guild = $guildMember->getGuild();
    $pendings = $guild->getPendings()->map(null, function($k, $v){
        return $v->pid;        
    });

    $refused = [];
    $ignored = [];
    foreach ($request->pids as $o) {
        $pid = $o->intval();
        if (false === isset($pendings->$pid)) {
            $ignored[] = $pid;
            continue;
        }
        
        $refused[] = $pendings->$pid;
    }

    $guild->removePendings($refused);
    $r = new Response;
    $count = count($refused);
    $r->pids->resize($count);
    for ($i = 0; $i < $count; $i++) {
        $r->pids->get($i)->intval($refused[$i]->pid);
    }

    $count = count($ignored);
    $r->pids->append($count);
    for ($j = 0; $j < $count; $j++) {
        $r->pids->get($i+$j)->intval($ignored[$j]);
    }
    $c->addReply($r);
};

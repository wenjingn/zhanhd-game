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
use Zhanhd\Object\User,
    Zhanhd\ReqRes\Relation\Friends\Response;

/**
 *
 */
return function(Client $c) {
    if (-1 === $this->parseParametersNone($c)) {
        return;
    }

    $p = $c->local->player;

    $relations = $p->getFriends();
    $r = new Response;
    $r->friends->resize($relations->count());
    
    $i = 0;
    foreach ($relations as $o) {
        $r->friends->get($i)->fromRelationObject($o, $this);
        $i++;
    }
    $c->addReply($r);
};

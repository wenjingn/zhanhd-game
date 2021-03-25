<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Stdlib\Object,
    System\Swoole\Client;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Config\Instance,
    Zhanhd\ReqRes\Task\Request,
    Zhanhd\Extension\Instance\Module;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;

    if (null === ($pl = $p->getLineups('gid')->get($request->gid->intval()))) {
        return $c->addReply($this->errorResponse->error('invalid lineup'));
    }
    if (false === $pl->getCaptain()) {
        return $c->addReply($this->errorResponse->error('empty captain'));
    }

    $diff = $request->task->flag->intval();
    if ($diff < Instance::DIFF_NORMAL || $diff > Instance::DIFF_CRAZY) {
        return $c->addReply($this->errorResponse->error('invalid task difficulty'));
    }

    if (null === ($instance = Store::get('ins'.$diff, $request->task->getFightId())) || 
            null === ($event = $instance->getEvent($request->task->eid->intval()))) {
        return $c->addReply($this->errorResponse->error('notfound task'));
    }
    
    $m = new Module($instance, $event, $pl);
    $m->process($c, $this);
};

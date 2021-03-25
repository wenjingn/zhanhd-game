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
use Zhanhd\ReqRes\Hero\Refine\Confirm\Request,
    Zhanhd\ReqRes\Hero\Refine\Confirm\Response,
    Zhanhd\Object\Player\Entity as PlayerEntity,
    Zhanhd\Config\Entity        as SourceEntity;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $pe = new PlayerEntity;
    if (false === $pe->findByPid($request->peid->intval(), $c->local->player->id)) {
        return $c->addReply($this->errorResponse->error('notfound hero'));
    }

    if ($pe->e->type != SourceEntity::TYPE_HERO) {
        return $c->addReply($this->errorResponse->error('invalid pe type'));
    }

    $refine = $pe->getRefine();
    if ($refine->count() == 0) {
        return $c->addReply($this->errorResponse->error('notfound refine'));
    }

    $pe->copyProp($refine, $pe->property);
    $refine->drop();
    $pe->save();

    $r = new Response;
    $r->refine->fromRefineObject($refine);
    return $c->addReply($r);
};

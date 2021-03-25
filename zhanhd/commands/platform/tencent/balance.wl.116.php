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
use Zhanhd\Extension\Platform\Tencent\Module as TencentModule,
    Zhanhd\Object\User;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParametersNone($c))) {
        return;
    }

    $u = new User;
    $u->find($c->uid->intval());
    if (false === $u->belongTencent()) {
        return $c->addReply($this->errorResponse->error('invalid platform'));
    }
    
    TencentModule::aspect($c, $u, $this);
};

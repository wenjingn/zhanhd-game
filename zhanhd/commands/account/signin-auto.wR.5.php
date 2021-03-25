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
use Zhanhd\ReqRes\Account\AutoSignin\Request,
    Zhanhd\ReqRes\Account\Signup\Response as SignupResponse,
    Zhanhd\Extension\Login\Module         as LoginModule,
    Zhanhd\Object\User,
    Zhanhd\Object\Player;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    // todo validate zone id
    $zone = $request->zone->intval();
    $c->zone->intval($zone);
    
    $u = new User;
    if (false === $u->decodeSecret($request->secret->strval())) {
        return $c->addReply($this->errorResponse->error('secret not matched'));
    }

    if (false === $u->validateStatus()) {
        return $c->addReply($this->errorResponse->error('user not valid'));
    }

    $u->loginSuccess(false);
    $c->uid->intval($u->id);

    // player login
    $p = new Player;
    if (false === $p->findByUid($zone, $u->id)) {
        $signupResponse = new SignupResponse;
        $signupResponse->user->fromUserObject($u);
        return $c->addReply($signupResponse);
    }

    LoginModule::aspect($p, $u, $c, $this);
};

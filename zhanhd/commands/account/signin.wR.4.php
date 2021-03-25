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
use Zhanhd\ReqRes\Account\Signin\Request,
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

    $platform = User::PF_ZHANHD;
    // todo: validate zone id
    $zone = $request->zone->intval();
    $c->zone->intval($zone);

    //
    $u = new User;

    // find user
    if (false === $u->findByLogin($platform, $request->login->strval())) {
        return $c->addReply($this->errorResponse->error('user not exists'));
    }

    // checking user flags
    if (false === $u->validateStatus()) {
        return $c->addReply($this->errorResponse->error('user not valid'));
    }

    // validating password
    if (false === password_verify($request->passwd->strval(), $u->passwd)) {
        return $c->addReply($this->errorResponse->error('password not match'));
    }

    // user login successfully
    $u->loginSuccess();
    $c->uid->intval($u->id);

    // player login
    $p = new Player;
    if (false === $p->findByUid($zone, $u->id)) {
        //if the user has no player in this zone then notify client to create it
        $signupResponse = new SignupResponse;
        $signupResponse->user->fromUserObject($u);
        return $c->addReply($signupResponse);
    }

    LoginModule::aspect($p, $u, $c, $this);
};

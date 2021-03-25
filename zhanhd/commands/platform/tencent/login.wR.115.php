<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Swoole\Client,
    System\Stdlib\Object;

/**
 *
 */
use Zhanhd\ReqRes\Platform\Tencent\Login\Request,
    Zhanhd\ReqRes\Account\Signup\Response as SignupResponse,
    Zhanhd\Extension\Login\Module         as LoginModule,
    Zhanhd\Library\Sdk\QQ,
    Zhanhd\Library\Sdk\WeChat,
    Zhanhd\Object\User,
    Zhanhd\Object\Player;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $platform = $request->platform->intval();
    if ($platform === User::PF_QQ) {
        $sdk = new QQ;
    } else if ($platform === User::PF_WECHAT) {
        $sdk = new WeChat;
    } else {
        return $c->addReply($this->errorResponse->error('invalid platform'));
    }
    // todo: validate zone id
    $zone = $request->zone->intval();
    $c->zone->intval($zone);

    $openid      = $request->openid->strval();
    $accessToken = $request->accessToken->strval();

    if (false === $sdk->verifyLogin($openid, $accessToken, $ret)) {
        return $c->addReply($this->errorResponse->error('sdk communication failure'));
    }

    if ($ret->ret != 0) {
        return $c->addReply($this->errorResponse->error('sdk authentication failure'));
    }

    // find user
    $u = new User;
    if (false === $u->findByLogin($platform, $openid)) {
        // if user not exists then create it
        $u->platform = $platform;
        $u->login    = $openid;
        $u->save();
        
        $u->loginSuccess($accessToken, $request->pf->strval(), $request->pfkey->strval(), $request->anotherToken->strval());
        $c->uid->intval($u->id);

        // and notify client to create a player
        $r = new SignupResponse;
        $r->user->fromUserObject($u);
        return $c->addReply($r);
    }

    if (false === $u->validateStatus()) {
        return $c->addReply($this->errorResponse->error('user not valid'));
    }
    $u->loginSuccess($accessToken, $request->pf->strval(), $request->pfkey->strval(), $request->anotherToken->strval());
    $c->uid->intval($u->id);

    $p = new Player;
    if (false === $p->findByUid($zone, $u->id)) {
        // if player not exists then notify client to create it
        $r = new SignupResponse;
        $r->user->fromUserObject($u);
        return $c->addReply($r);
    }

    LoginModule::aspect($p, $u, $c, $this);
};

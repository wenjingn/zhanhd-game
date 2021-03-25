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
use Zhanhd\ReqRes\Platform\Baidu\Login\Request,
    Zhanhd\ReqRes\Account\Signup\Response as SignupResponse,
    Zhanhd\Extension\Login\Module         as LoginModule,
    Zhanhd\Library\Sdk\Baidu,
    Zhanhd\Object\User,
    Zhanhd\Object\Player;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $platform = User::PF_BAIDU;
    // todo: validate zone id
    $zone = $request->zone->intval();
    $c->zone->intval($zone);

    $accessToken = $request->accessToken->strval();
    $sdk = new Baidu;
    if (false === $sdk->verifyLogin($accessToken, $ret)) {
        return $c->addReply($this->errorResponse->error('sdk communication failure'));
    }

    if ($ret->ResultCode != 1) {
        return $c->addReply($this->errorResponse->error('sdk authentication failure'));
    }

    $content = urldecode($ret->Content);
    if ($ret->Sign != $sdk->signMd5($ret->ResultCode, $content)) {
        return $c->addReply($this->errorResponse->error('sdk sign error'));
    }
    $ret = json_decode(base64_decode($content));
    $uid = $ret->UID;

    if ($uid != $request->uid->strval()) {
        return $c->addReply($this->errorResponse->error('uid invalid'));
    }

    // find user
    $u = new User;
    if (false === $u->findByLogin($platform, $uid)) {
        // if user not exists then create it
        $u->platform = $platform;
        $u->login    = $uid;
        $u->save();
        
        $u->loginSuccess($accessToken);
        $c->uid->intval($u->id);

        // and notify client to create a player
        $r = new SignupResponse;
        $r->user->fromUserObject($u);
        return $c->addReply($r);
    }

    if (false === $u->validateStatus()) {
        return $c->addReply($this->errorResponse->error('user not valid'));
    }
    $u->loginSuccess($accessToken);
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

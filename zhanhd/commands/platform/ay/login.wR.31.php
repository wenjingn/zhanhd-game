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
use Zhanhd\ReqRes\Platform\AY\Login\Request,
    Zhanhd\ReqRes\Account\Signup\Response as SignupResponse,
    Zhanhd\Extension\Login\Module         as LoginModule,
    Zhanhd\Library\Sdk\AY,
    Zhanhd\Object\User,
    Zhanhd\Object\Player;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $platform = User::PF_AY;
    $zone = $request->zone->intval();
    $c->zone->intval($zone);

    $accountid = $request->accountid->strval();
    $sessionid = $request->sessionid->strval();
    $sdk = new AY;

    if (false === $sdk->verifyLogin($accountid, $sessionid, $ret)) {
        return $c->addReply($this->errorResponse->error('sdk communication failure'));
    }

    if ($ret->code) {
        return $c->addReply($this->errorResponse->error('sdk authentication failure'));
    }

    $u = new User;
    if (false === $u->findByLogin($platform, $accountid)) {
        // if user not exists then create it
        $u->platform = $platform;
        $u->login = $accountid;
        $u->save();

        $u->loginSuccess($sessionid);
        $c->uid->intval($u->id);

        // notify client to create a player
        $r = new SignupResponse;
        $r->user->fromUserObject($u);
        return $c->addReply($r);
    }
    
    if (false === $u->validateStatus()) {
        return $c->addReply($this->errorResponse->error('user not valid'));
    }
    $u->loginSuccess($sessionid);
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

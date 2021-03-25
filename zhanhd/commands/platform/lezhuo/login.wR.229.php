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
use Zhanhd\ReqRes\Platform\Lezhuo\Login\Request,
    Zhanhd\ReqRes\Account\Signup\Response as SignupResponse,
    Zhanhd\Extension\Login\Module         as LoginModule,
    Zhanhd\Object\User,
    Zhanhd\Object\Player,
    Zhanhd\Library\Sdk\Lezhuo;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $platform = User::PF_LEZHUO;
    $zone = $request->zone->intval();
    $c->zone->intval($zone);

    $appvers = $request->appvers->strval();
    $token   = $request->token->strval();
    $device  = $request->device->strval();
    $deviceuuid = $request->deviceuuid->strval();
    $mixcode = $request->mixcode->strval();
    $os      = $request->os->strval();
    $osvers  = $request->osvers->strval();
    $from    = $request->from->strval();
    $cpscid  = $request->cpscid->strval();

    $sdk = new Lezhuo;
    if (false === $sdk->getInfo($appvers, $token, $device, $deviceuuid, $mixcode, $os, $osvers, $ret)) {
        return $c->addReply($this->errorResponse->error('sdk communication failure'));
    }

    if ($ret->state != 0) {
        return $c->addReply($this->errorResponse->error('sdk authentication failure'));
    }

    $u = new User;
    if (false === $u->findByLogin($platform, $ret->data->account)) {
        // if user not exists then create it
        $u->platform = $platform;
        $u->login    = $ret->data->account;
        $u->lastLogin = $this->ustime;
        $u->passwd   = $token;
        $u->profile->appvers = $appvers;
        $u->profile->device  = $device;
        $u->profile->deviceuuid = $deviceuuid;
        $u->profile->mixcode = $mixcode;
        $u->profile->os      = $os;
        $u->profile->osvers  = $osvers;
        $u->profile->ip      = $c->host->strval();
        $u->profile->from    = $from;
        $u->profile->cpscid  = $cpscid;
        $u->save();
        $c->uid->intval($u->id);
        goto notify;
    }

    if (false === $u->validateStatus()) {
        return $c->addReply($this->errorResponse->error('user not valid'));
    }
    $u->lastLogin = $this->ustime;
    $u->passwd    = $token;
    $u->profile->appvers = $appvers;
    $u->profile->device  = $device;
    $u->profile->deviceuuid = $deviceuuid;
    $u->profile->mixcode = $mixcode;
    $u->profile->os      = $os;
    $u->profile->osvers  = $osvers;
    $u->profile->ip      = $c->host->strval();
    $u->profile->from    = $from;
    $u->profile->cpscid  = $cpscid;
    $u->save();
    $c->uid->intval($u->id);

    $p = new Player;
    if (false === $p->findByUid($zone, $u->id)) {
        goto notify;
    }

    return LoginModule::aspect($p, $u, $c, $this);
notify:
    $r = new SignupResponse;
    $r->user->fromUserObject($u);
    $c->addReply($r);
};

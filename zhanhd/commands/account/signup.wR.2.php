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
use Zhanhd\ReqRes\Account\Signup\Request,
    Zhanhd\ReqRes\Account\Signup\Response,
    Zhanhd\Object\User;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $platform = User::PF_ZHANHD;
    // to do : validate zone id
    $zone = $request->zone->intval();
    $c->zone->intval($zone);

    // username
    $login  = $request->login->strval();
    $strlen = $request->login->strlen();
    if ($strlen < 4) {
        return $c->addReply($this->errorResponse->error('username too short'));
    } else if ($strlen > 15) {
        return $c->addReply($this->errorResponse->error('username too long'));
    } else if (preg_match('~[^a-z0-9]+~i', $login)) {
        return $c->addReply($this->errorResponse->error('username not valid'));
    } else if (User::loginExists($this->pdo, $platform, $login)) {
        return $c->addReply($this->errorResponse->error('username already exists'));
    }

    // password
    $rawpswd = $request->passwd->strval();
    if ($request->passwd->strlen() < 6) {
        return $c->addReply($this->errorResponse->error('password too short'));
    }

    // email
    $email = $request->email->strval();
    if ($request->email->strlen() && false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $c->addReply($this->errorResponse->error('email not valid'));
    }

    $u = new User;
    $u->platform = $platform;
    $u->login    = $login;
    $u->rawpswd  = $rawpswd;
    $u->email    = $email;
    $u->save();
    
    $u->loginSuccess();
    $c->uid->intval($u->id);

    // send 
    $r = new Response;
    $r->user->fromUserObject($u);
    $c->addReply($r);
};

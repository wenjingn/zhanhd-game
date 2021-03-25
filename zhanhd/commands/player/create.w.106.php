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
use Zhanhd\ReqRes\Player\Create\Request,
    Zhanhd\ReqRes\Player\Create\Response,
    Zhanhd\Extension\Login\Module        as LoginModule,
    Zhanhd\Extension\BadwordFilter,
    Zhanhd\Extension\Service\Module      as ServiceModule,
    Zhanhd\Object\User,
    Zhanhd\Object\Player,
    Zhanhd\Library\Sdk\Lezhuo;

/**
 *
 */
$veryInitEid = 1101108;

/**
 *
 */
return function(Client $c) use ($veryInitEid) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $u = new User;
    if (false === $u->find($c->uid->intval())) {
        return $c->addReply($this->errorResponse->error('auth failure'));
    }

    $nick = trim($request->nick->strval());
    if (empty($nick)) {
        return $c->addReply($this->errorResponse->error('nickname empty'));
    }

    if (mb_strlen($nick) > 10) {
        return $c->addReply($this->errorResponse->error('nickname too long'));
    }

    $filter = new BadwordFilter;
    if (false === $filter->check($nick)) {
        return $c->addReply($this->errorResponse->error('nickname contains badword'));
    }

    $p = new Player;
    if ($p->nameExists($c->zone->intval(), $nick)) {
        return $c->addReply($this->errorResponse->error('player-name already exists')); 
    }

    $p->veryInitEid = $veryInitEid;
    $p->uid = $u->id;
    $p->zone = $c->zone->intval();
    $p->name = $nick;
    
    $retry = 10;
    do {
        $invcode = Player::generateInvcode();
        $retry--;
    } while(($invcodeExists = Player::invcodeExists($this->pdo, $invcode)) && $retry > 0);

    if ($invcodeExists) {
        return $c->addReply($this->errorResponse->error('player create failure'));
    }

    try {
        $p->invcode = $invcode;
        $p->save();
    } catch (PDOException $e) {
        return $c->addReply($this->errorResponse->error('player create failure'));
    }

    ServiceModule::forGreener($p, $this);
    LoginModule::aspect($p, $u, $c, $this, true);
    $r = new Response;
    $c->addReply($r);
    if ($u->platform == User::PF_LEZHUO) {
        $sdk = new Lezhuo;
        $sdk->postRoleCreate($u, $p, $ret);
    }
};

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
use Zhanhd\ReqRes\Invite\Request,
    Zhanhd\ReqRes\Invite\Response,
    Zhanhd\ReqRes\Invite\NotifyResponse,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Invite    as PlayerInvite,
    Zhanhd\Object\Player\Coherence as PlayerCoherence,
    Zhanhd\Extension\Reward\Module as RewardModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }
    
    $p = $c->local->player;

    if ($request->invcode->strlen() != Player::INVCODE_LENGTH) {
        return $c->addReply($this->errorResponse->error('invalid invcode-length'));
    }

    $invcode = $request->invcode->strval();
    if ($p->invcode == $invcode) {
        return $c->addReply($this->errorResponse->error('deprecated invite yourself'));
    }

    if ($p->isInvited()) {
        return $c->addReply($this->errorResponse->error('already be-invited'));
    }

    $inviter = new Player;
    if (false === $inviter->findByInvcode($invcode)) {
        return $c->addReply($this->errorResponse->error('notfound player'));
    }

    if (false === PlayerCoherence::increase($this->pdo, $inviter->id, 'invcount', 1, PlayerCoherence::INVITE_LIMIT)) {
        return $c->addReply($this->errorResponse->error('high-limit target invcount'));
    }
    
    $pi = new PlayerInvite;
    $pi->pid = $inviter->id;
    $pi->invitee = $p->id;
    $pi->save();

    $notify = new NotifyResponse;
    $notify->invcount->intval(PlayerCoherence::get($this->pdo, $inviter->id, 'invcount'));
    $this->sendTo($inviter->id, $notify);

    $r = new Response;
    RewardModule::aspect($p, [10 => 188], $r->reward, $c, $this);
    $c->addReply($r);
};

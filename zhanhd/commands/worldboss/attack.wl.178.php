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
use Zhanhd\ReqRes\WorldBoss\Attack\Request,
    Zhanhd\ReqRes\WorldBoss\Attack\Response,
    Zhanhd\ReqRes\SysMsgResponse,
    Zhanhd\Task\WorldBoss\Request as TaskRequest,
    Zhanhd\Config\Store,
    Zhanhd\Object\Player\Reward       as PlayerReward,
    Zhanhd\Extension\WorldBoss\Module as WorldBossModule,
    Zhanhd\Extension\Combat\Module    as CombatModule;

/**
 *
 */
return function(Client $c){
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    /* lineup check */
    if (null === ($pl = $p->getLineups('gid')->get($request->gid->intval()))) {
        return $c->addReply($this->errorResponse->error('invalid lineup'));
    }

    $m = new WorldBossModule($this);
    if (false === $m->checkTime()) {
        return $c->addReply($this->errorResponse->error('invalid worldboss time'));
    }

    if ($this->ustime - $p->recent->worldboss < 120000000) {
        return $c->addReply($this->errorResponse->error('limit worldboss interval'));
    }

    $m = new WorldBossModule($this);
    $bi = $m->getBossInfo();
    if ($bi->chp <= 0) {
        return $c->addReply($this->errorResponse->error('finish worldboss'));
    }

    if (null === ($boss = Store::get('worldboss', $bi->id))) {
        throw new Exception('notfound worldboss');
    }

    $l = $boss->getNpcLineup($bi->lvl);
    $r = new Response;
    $ret = (new CombatModule)->combat($pl, $l, $r->combat, function($attackers, $defenders) use ($bi){
        $boss = $defenders->get(4);
        $boss->setRawHpoint($bi->rhp);
        $boss->setHpoint($bi->chp);
    });
    $ret = $m->attack($p->id, $ret->attacker);

    if ($ret === false) {
        return $c->addReply($this->errorResponse->error('finish worldboss'));
    }


    $treq = new TaskRequest;
    $treq->bosshp->intval($ret->bosshp<0?0:$ret->bosshp);
    $treq->damage->intval($ret->damage);
    $treq->dmgsum->intval($ret->dmgsum);
    $this->task('worldboss-attack', $treq);

    if ($ret->bosshp <= 0) {
        $pr = new PlayerReward;
        $pr->pid = $p->id;
        $pr->from = PlayerReward::from_bosskill;
        $pr->intval = $boss->id;
        $pr->save();
        $this->task('worldboss-kill');

        /* broadcast */
        $smr = new SysMsgResponse;
        $smr->format(3, 2, array($p->name));
        $this->task('broadcast-global', $smr);
    }

    $p->recent->worldboss = $this->ustime;
    $p->recent->save();
    $r->cycle->intval(120);
    $r->dmgsum->intval($ret->dmgsum);
    $c->addReply($r);
};

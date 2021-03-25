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
use Zhanhd\Object\Player,
    Zhanhd\ReqRes\Rank\PlayerResponse,
    Zhanhd\Extension\Rank\Module as RankModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === $this->parseParametersNone($c)) {
        return;
    }

    $myPowerRank = $myLevelRank = 0;

    $p = $c->local->player;
    $m = new RankModule;
    $r = new PlayerResponse;

    $m->using(RankModule::KEY_PLAYER_POWER);
    $range = $m->range(0, 9);
    $r->powerRanks->resize(count($range));
    $i = 0; foreach ($range as $pid => $score) {
        $t = new Player;
        if ($t->find($pid)) {
            $r->powerRanks->get($i)->fromPlayerObject($t, $i+1, 0, $m->parse($score));
        }

        if ($pid == $p->id) {
            $myPowerRank = $i+1;
        }

        $i++;
    }

    $m->using(RankModule::KEY_PLAYER_LEVEL);
    $range = $m->range(0, 9);
    $r->levelRanks->resize(count($range));
    $r->lineups->resize(count($range));
    $i = 0; foreach ($range as $pid => $score) {
        $t = new Player;
        if ($t->find($pid)) {
            $r->levelRanks->get($i)->fromPlayerObject($t, $i+1, $m->parse($score), 0);
            $r->lineups->get($i)->fromObject($t->getLineup(1));
        }

        if ($pid == $p->id) {
            $myLevelRank = $i+1;
        }

        $i++;
    }

    if ($myPowerRank == 0) {
        $m->using(RankModule::KEY_PLAYER_POWER);
        $myPowerRank = $m->rank($p->id) + 1;
    }

    if ($myLevelRank == 0) {
        $m->using(RankModule::KEY_PLAYER_LEVEL);
        $myLevelRank = $m->rank($p->id) + 1;
    }

    $r->myPowerRank->intval($myPowerRank);
    $r->myLevelRank->intval($myLevelRank);

    $pl = $p->getLineups('gid')->get(1);
    $r->myLevel->intval($pl->getLvlsum());
    $r->myPower->intval($pl->getPower());

    $c->addReply($r);
};

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
use Zhanhd\Object\Guild,
    Zhanhd\ReqRes\Rank\GuildResponse,
    Zhanhd\Extension\Rank\Module as RankModule;

/**
 *
 */
return function(Client $c) {
    if (-1 === $this->parseParametersNone($c)) {
        return;
    }

    $p = $c->local->player;
    $m = new RankModule;
    $r = new GuildResponse;
    $x = $p->getGuildMember();

    $m->using(RankModule::KEY_GUILD_RANK);
    $range = $m->range(0, 9);
    $mrank = 0;

    $r->ranks->resize(count($range));
    $i = 0; foreach ($range as $gid => $score) {
        $t = new Guild;
        if ($t->find($gid)) {
            $r->ranks->get($i)->fromGuildObject($t, $i+1, $score);
        }

        if ($x && $gid == $x->gid) {
            $mrank = $i+1;
        }

        $i++;
    }

    if ($x) {
        $r->level->intval($x->getGuild()->lvl);
    }

    $r->rank->intval($mrank);
    $c->addReply($r);
};

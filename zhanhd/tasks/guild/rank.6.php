<?php
/**
 * $Id$
 */

/**
 *
 */
use Zhanhd\Object\Guild,
    Zhanhd\Task\UpdateGuildRank,
    Zhanhd\Extension\Rank\Module as RankModule;

/**
 *
 */
return function($data) {
    $x = new UpdateGuildRank;
    $x->decode($data);

    $guild = new Guild;
    if (false === $guild->find($x->gid->intval())) {
        return;
    }

    $m = new RankModule;
    $m->using(RankModule::KEY_GUILD_RANK);
    $m->pushGuild($guild->id, $guild->getRankScore());
};

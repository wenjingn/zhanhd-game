<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Logout;

/**
 *
 */
use System\Swoole\Client;

/**
 *
 */
use Zhanhd\Object\Player;

/**
 *
 */
class Module
{
    /**
     * @param Client $c
     * @param Object $g
     * @return void
     */
    public static function aspect(Client $c, $g)
    {
        if (null === ($p = $c->local->player)) {
            $p = new Player;
            $p->find($c->login->intval());
        }

        $p->logout = ustime();
        $dur = (int)(($p->logout-$p->lastLogin)/1000000);
        $p->counter->onlineDur += $dur;
        $p->counterCycle->onlineDur += $dur;

        if ($p->counterCycle->nextOnlineRewardAccepted) {
            $p->counterCycle->nextOnlineRewardAccepted = max(0, $p->counterCycle->nextOnlineRewardAccepted - $dur);
        }

        $p->save();

        $g->redis->hdel('zhanhd:ht:onlines', $p->id);
        $g->redis->hdel('zhanhd:ht:onlines:workerid', $p->id);
        $g->redis->hdel(sprintf('zhanhd:ht:worldboss:players:%s', date('Ymd')), $p->id);
        $guildMember = $p->getGuildMember();
        if ($guildMember) {
            $g->redis->hdel(sprintf('zhanhd:ht:guildmembers:%d', $guildMember->gid), $guildMember->pid);
        }
    }
}

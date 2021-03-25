<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Login;

/**
 *
 */
use System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Extension\Achievement\Module as AchievementModule,
    Zhanhd\Extension\NewzoneMission\Module as NewzoneMissionModule,
    Zhanhd\Extension\WeekMission\Module as WeekMissionModule,
    Zhanhd\ReqRes\Account\InitResponse,
    Zhanhd\ReqRes\Relation\Friends\Response,
    Zhanhd\Extension\Mail\Module        as MailModule,
    Zhanhd\Extension\Relation\Module    as RelationModule,
    Zhanhd\Extension\Deposit\Module     as DepositModule,
    Zhanhd\Extension\Platform\Tencent\Module as TencentModule,
    Zhanhd\Config\WeekMission,
    Zhanhd\Config\NewzoneMission,
    Zhanhd\Object\Zone\Daily    as ZoneDaily,
    Zhanhd\Object\Order,
    Zhanhd\Object\User,
    Zhanhd\Object\Player,
    Zhanhd\Object\Guild\Member  as GuildMember,
    Zhanhd\Object\Guild\Pending as GuildPending,
    Zhanhd\ReqRes\Guild\Apply\Notify,
    Zhanhd\Library\Sdk\Lezhuo;

/**
 *
 */
class Module
{
    /**
     * @return void
     */
    public static function aspect($p, $u, $c, $global, $isFirst = false)
    {
        if ($x = $global->redis->hget('zhanhd:ht:onlines', $p->id)) {
            if ($x != $c->sock->intval()) {
                $global->close($x);
            }
        }

        $global->redis->hset('zhanhd:ht:onlines', $p->id, $c->sock->intval());
        
        $online = $global->redis->hlen('zhanhd:ht:onlines');
        $zoneDaily = new ZoneDaily;
        if (false === $zoneDaily->find($global->date, 'onlinePeak')) {
            $zoneDaily->date = $global->date;
            $zoneDaily->k    = 'onlinePeak';
        }
        if ($zoneDaily->v < $online) {
            $zoneDaily->updateOnlinePeak($online);
        }

        $global->redis->hset('zhanhd:ht:onlines:workerid', $p->id, $c->work->intval());
        $guildMember = $p->getGuildMember();
        if ($guildMember) {
            $global->redis->hset(sprintf('zhanhd:ht:guildmembers:%d', $guildMember->gid), $p->id, $c->sock->intval());
        }

        if ($p->counterCycle->signin < 1) {
            $p->counter->loginDays++;
            $p->counterCycle->loginDays++;
            $p->counterWeekly->loginDays++;
        }
        $p->lastLogin = $global->ustime;
        $p->logout = 0;
        $p->save();
        $c->login->intval($p->id);
        $c->local->player = $p;
        
        /* sequence is important,initReponse must be very first */
        $r = new InitResponse;
        $r->fromObject($u, $p, $global, $c);
        $c->addReply($r);
        

        if (false === $isFirst) {
            $orders = Order::getsByPid($global->pdo, $p->id);
            foreach ($orders as $order) {
                $order->status = Order::STATUS_SUCCESS;
                $order->save();

                DepositModule::aspect($c, $order, $global);
            }
        }

        $am = new AchievementModule($p, $global);
        $am->trigger((new Object)->import([
            'cmd' => 'signin',            
        ]));
        $am->trigger((new Object)->import([
            'cmd' => 'memcard',            
        ]));

        /* new zone mission */
        NewzoneMissionModule::trigger($p, $global, NewzoneMission::TYPE_LOGIN);
        WeekMissionModule::trigger($p, $global, WeekMission::TYPE_LOGIN, $p->counterWeekly->loginDays);

        $c->mixed->maxMessageId->intval($p->profile->lastReadMessageId);
        MailModule::aspect($c, $global);
        RelationModule::aspect($c, $global);

        /* query tencent sdk */
        if ($u->belongTencent()) {
            TencentModule::aspect($c, $u, $global);
        }

        if ($u->platform == User::PF_LEZHUO) {
            $sdk = new Lezhuo;
            $sdk->postRoleLogon($u, $p, $ret);
        }
    
        /* friend list */
        $relations = $p->getFriends();
        $r = new Response;
        $r->friends->resize($relations->count());
        
        $i = 0;
        foreach ($relations as $o) {
            $r->friends->get($i)->fromRelationObject($o, $global);
            $i++;
        }
        $c->addReply($r);

        if ($guildMember && $guildMember->isGuildManager()) {
            $pendings = GuildPending::getsByGid($global->pdo, $guildMember->gid);
            $count = $pendings->count();
            if ($count) {
                $r = new Notify;
                $r->applies->resize($count);
                foreach ($r->applies as $i => $o) {
                    $pending = $pendings->get($i);
                    $player = new Player;
                    $player->find($pending->pid);
                    $pending->convertMember($player);
                    $o->fromGuildMemberObject($pending);
                }
                $c->addReply($r);
            }
        }
    }
}

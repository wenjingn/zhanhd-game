<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Deposit;

/**
 *
 */
use System\Swoole\Client,
    System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Config\NewzoneMission,
    Zhanhd\Config\WeekMission,
    Zhanhd\Object\User,
    Zhanhd\ReqRes\DepositResponse,
    Zhanhd\ReqRes\AccumDepositResponse,
    Zhanhd\ReqRes\Building\ResourceResponse,
    Zhanhd\Extension\Achievement\Module as AchievementModule,
    Zhanhd\Extension\NewzoneMission\Module as NewzoneMissionModule,
    Zhanhd\Extension\WeekMission\Module    as WeekMissionModule,
    Zhanhd\Library\Sdk\Lezhuo;

/**
 *
 */
class Module
{
    /**
     * @return void
     */
    public static function aspect(Client $c, $order, $global)
    {
        $merchandise = Store::get('merchandise', $order->merchandise);
        $p = $c->local->player;
        $r = new DepositResponse;
        $r->merchandise->intval($merchandise->id);
        if ($merchandise->id == 101) {
            if ($global->ustime > $p->profile->monthlyCardExpire) {
                $p->profile->monthlyCardExpire = $global->ustime + 86400000000 * 30;
            } else {
                $p->profile->monthlyCardExpire += 86400000000 * 30;
            }
            $r->num->intval((integer)(($p->profile->monthlyCardExpire - $global->ustime) / 1000000));

            $am = new AchievementModule($p, $global);
            $am->trigger((new Object)->import([
                'cmd' => 'memcard',
            ]));
        } else {
            $ckey = $merchandise->getCounterKey();
            $diamond = $merchandise->getDiamond($p->counter->$ckey == 0);
            $p->counter->$ckey++;
            $p->incrGold($diamond);

            $deposit = $merchandise->price*10;
            $p->deposit += $deposit;
            $p->counterCycle->diamondDeposit += $deposit;
            $p->counterWeekly->diamondDeposit += $deposit;
            $r->num->intval($diamond);

            $accumDepositResponse = new AccumDepositResponse;
            $accumDepositResponse->deposit->intval($p->deposit);
            $c->addReply($accumDepositResponse);

            $resourceResponse = new ResourceResponse;
            $resourceResponse->retval->fromOwnerObject($p);
            $c->addReply($resourceResponse);
            NewzoneMissionModule::trigger($p, $global, NewzoneMission::TYPE_DEPOSIT, $p->deposit/10);
            WeekMissionModule::trigger($p, $global, WeekMission::TYPE_DEPOSIT, $p->counterWeekly->diamondDeposit/10);
        }


        $k = $merchandise->getRechargeRewardKey();
        $p->counterCycle->$k++;

        $p->save();
        $c->addReply($r);

        $u = new User;
        $u->find($p->uid);
        if ($u->platform == User::PF_LEZHUO) {
            $sdk = new Lezhuo;
            $sdk->postPay($u, $p, $order, $ret);
        }
    }
}

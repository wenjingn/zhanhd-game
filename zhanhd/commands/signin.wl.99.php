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
use Zhanhd\ReqRes\Signin\Request,
    Zhanhd\ReqRes\Signin\Response,
    Zhanhd\Config\Store,
    Zhanhd\Config\SigninReward,
    Zhanhd\Extension\Reward\Module as RewardModule;

/**
 *
 */
return function (Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $flag = $request->flag->intval();
    if ($flag == Request::FLAG_SIGNIN) {
        if ($p->counterCycle->sign) {
            return $c->addReply($this->errorResponse->error('signin already today'));
        }
        
        $p->counterCycle->sign++;
        $p->counterCycle->save();
        
        $p->counterMonthly->sign++;
        $p->counterMonthly->save();
        $day = $p->counterMonthly->sign;
        $rewards = Store::get('signin', $day)->getRewards($p->isMember());
    } else {
        if ($p->counterCycle->greenerSignin) {
            return $c->addReply($this->errorResponse->error('already greener-signin today'));
        }

        if ($p->counter->greenerSignin >= 7) {
            return $c->addReply($this->errorResponse->error('full greener-signin'));
        }

        $p->counterCycle->greenerSignin++;
        $p->counterCycle->save();

        $p->counter->greenerSignin++;
        $p->counter->save();
        $day = $p->counter->greenerSignin;
        $rewards = Store::get('greenerReward', $day)->getRewards();
    }

    $r = new Response;
    $r->flag->intval($flag);
    $r->day->intval($day);
    if ($flag == Request::FLAG_SIGNIN) {
        $r->day->bitset(1 << 5);
    } else {
        $r->day->bitset(1 << 3);
    }
    RewardModule::aspect($p, $rewards, $r->rewards, $c, $this);
    $c->addReply($r);
};

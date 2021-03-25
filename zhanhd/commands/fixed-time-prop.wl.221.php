<?php
/**
 * $Id$
 */

/**
 *
 */
use System\Swoole\Client,
    System\Stdlib\Object;

/**
 *
 */
use Zhanhd\ReqRes\FixedTimeProp\Request,
    Zhanhd\ReqRes\FixedTimePropResponse,
    Zhanhd\ReqRes\RewardInfo,
    Zhanhd\Config\Store,
    Zhanhd\Extension\Reward\Module as RewardModule;

/**
 * @var array
 */
$fixedTimeProps = [
    12 => array(
        410106 => 1,
    ),
    18 => array(
        410106 => 1,
    ),
    21 => array(
        410106 => 1,
    ),
];

/**
 * what if more rewards
 */
return function(Client $c) use ($fixedTimeProps) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $hour = date('G', $this->ustime / 1000000);
    $flag = false;
    foreach ($fixedTimeProps as $what => $source) {
        if ($hour == $what) {
            $flag = true;
            break;
        }
    }

    if ($flag === false) {
        return $c->addReply($this->errorResponse->error('invalid fixed time'));
    }

    $p = $c->local->player;
    $k = sprintf('fixed-time-prop-%d', $hour);

    if ($p->counterCycle->$k) {
        return $c->addReply($this->errorResponse->error('already done'));
    }

    /* init fake RewardInfo */
    RewardModule::aspect($p, $source, new RewardInfo, $c, $this);

    /* send real response */
    $r = new FixedTimePropResponse;;
    $r->id       ->intval($request->id->intval());
    $r->prop->eid->intval(key($source));
    $r->prop->num->intval(current($source));
    $c->addReply($r);

    $p->counterCycle->$k = 1;
    $p->counterCycle->save();
};

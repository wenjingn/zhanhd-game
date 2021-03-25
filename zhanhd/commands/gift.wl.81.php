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
use Zhanhd\ReqRes\Gift\Request,
    Zhanhd\ReqRes\Gift\Response,
    Zhanhd\Config\Store,
    Zhanhd\Extension\Reward\Module as RewardModule,
    Zhanhd\Library\Curl;

/**
 *
 */
return function(Client $c) {
    if (-1 === ($request = $this->parseParameters($c, new Request))) {
        return;
    }

    $p = $c->local->player;
    $series = $request->serial->strval();
    $separates = explode('-', $series);
    if (count($separates) > 2) {
        return $c->addReply($this->errorResponse->error('invalid argvs'));
    }
    foreach ($separates as $part) {
        if (preg_match('/[^a-zA-Z0-9]/', $part, $ret)) {
            return $c->addReply($this->errorResponse->error('invalid argvs'));
        }
    }

    $appId = 1;
    $appKey = '63e3dac3b39013ac9cc6e4ed5e603c53';
    $time = (int)($this->ustime/1000000);
    $sign = md5($appId.$series.$time.$p->uid.$p->zone.$p->id.$appKey);
    $curl = new Curl;
    $res = $curl->setHost('lezhuo.api.fenglingame.com')->setPath('/gift/open')->setPost([
        'sign' => $sign,
        'uid'  => $p->uid,
        'zone' => $p->zone,
        'pid'  => $p->id,
        'time' => $time,
        'series' => $series,
    ])->request();

    if ($res === false) {
        return $c->addReply($this->errorResponse->error('netfail'));
    }

    $json = json_decode($res);
    if (null === $res) {
        return $c->addReply($this->errorResponse->error('netfail'));
    }

    if ($json->errno) {
        switch ($json->errno) {
        case 1:
        case 2:
        case 3:
            return $c->addReply($this->errorResponse->error('invalid argvs'));
        case 4:
        case 8:
            return $c->addReply($this->errorResponse->error('notfound gift'));
        case 5:
            return $c->addReply($this->errorResponse->error('already accepted gift'));
        case 6:
            return $c->addReply($this->errorResponse->error('notrelease gift'));
        case 7:
            return $c->addReply($this->errorResponse->error('expire gift'));
        case 9:
            return $c->addReply($this->errorResponse->error('already used gift-series'));
        }
        return $c->addReply($this->errorResponse->error('failure'));
    }

    $r = new Response;
    RewardModule::aspect($p, $json->gift, $r->rewards, $c, $this);
    $c->addReply($r);
};

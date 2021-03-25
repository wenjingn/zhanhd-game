<?php
/**
 * $Id$
 */

/**
 *
 */
use Zhanhd\Task\WorldBoss\Request,
    Zhanhd\ReqRes\WorldBoss\Notify\MetaResponse,
    Zhanhd\ReqRes\WorldBoss\Notify\RankResponse;

/**
 *
 */
return function($data){
    $req = new Request;
    $req->decode($data);

    $date = date('Ymd');
    $pykey = sprintf('zhanhd:ht:worldboss:players:%s', $date);
    $pids = $this->redis->hgetall($pykey);

    $damage = $req->damage->intval();
    $dmgsum = $req->dmgsum->intval();
    $res = new MetaResponse;
    $res->damage->intval($damage);
    $res->bosshp->intval($req->bosshp->intval());
    foreach ($pids as $pid => $ignore) {
        $this->sendTo($pid, $res);
    }

    $stkey = sprintf('zhanhd:st:worldboss:%s', $date);
    $pids = $this->redis->zrevrangebyscore($stkey, $dmgsum, $dmgsum-$damage);
    $res = new RankResponse;
    $rank = -1;
    foreach ($pids as $pid) {
        if ($rank == -1) {
            $rank = $this->redis->zrevrank($stkey, $pid);
        }
        $res->rank->intval(++$rank);
        $this->sendTo($pid, $res);
    }
};

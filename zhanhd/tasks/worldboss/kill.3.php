<?php
/**
 * $Id$
 */

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Reward    as PlayerReward,
    Zhanhd\Object\Player\Coherence as PlayerCoherence,
    Zhanhd\ReqRes\Relation\FriendShipUpdateResponse,
    Zhanhd\ReqRes\SysMsgResponse,
    Zhanhd\Extension\WorldBoss\Module;

/**
 *
 */
return function(){
    $m = new Module($this);
    $bi = $m->getBossInfo();
    if ($bi->over) {
        return;
    }

    $i = 0; $rank = 1;
    $msgSent = false;
    while (true) {
        $list = $m->getRankList($i, $i+99);
        if (empty($list)) {
            break;
        }

        foreach ($list as $pid => $damage) {
            $pr = new PlayerReward;
            $pr->pid = $pid;
            $pr->intval = $rank;
            $pr->strval = $damage;
            $pr->from = PlayerReward::from_wdboss;
            $pr->save();

            $score = (int)($damage/1000);
            PlayerCoherence::increase($this->pdo, $pid, 'friendship', $score);
            $notify = new FriendShipUpdateResponse;
            $notify->flag->intval(0);
            $notify->value->intval($score);
            $this->sendTo($pid, $notify);
            $rank++;

            /* broadcast */
            if ($i == 0 && $msgSent === false) {
                $killer = new Player;
                if ($killer->find($pid)) {
                    $smr = new SysMsgResponse;
                    $smr->format(2, 2, array($killer->name, $damage));
                    $msg = $smr->encode();
                    $this->getServer()->scanFds(function($s, $fd, $data){
                        $s->swServer->send($fd, $data);
                    }, $msg);
                }

                $msgSent = true;
            }
        }

        $i+=100;
    }

    $m->over();
};

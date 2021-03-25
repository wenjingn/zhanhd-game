<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Mail;

/**
 *
 */
use System\Swoole\Client;

/**
 *
 */
use Zhanhd\ReqRes\MessageMail\NotifyResponse as MessageMailResponse,
    Zhanhd\ReqRes\RewardMail\NotifyResponse  as RewardMailResponse,
    Zhanhd\Object\Message,
    Zhanhd\Object\Player,
    Zhanhd\Object\Player\Reward as PlayerReward;

/**
 *
 */
class Module
{
    /**
     * @param Client $c
     * @param Object $global
     * @return void
     */
    public static function aspect(Client $c, $global)
    {
        $p = $c->local->player;
        $messages = $p->getMessages($c->mixed->maxMessageId->intval());
        if ($count = $messages->count()) {
            $r = new MessageMailResponse;
            $c->addReply($r);
            $c->mixed->maxMessageId->intval($messages->get($count-1)->id);
        }

        $rewards = $p->getRewards($c->mixed->maxRewardId->intval());
        if ($n = $rewards->count()) {
            $r = new RewardMailResponse;
            $r->mails->resize($n);
            foreach ($r->mails as $i => $o) {
                $o->fromPlayerRewardObject($rewards->get($i), $global);
            }
            $c->addReply($r);
            $c->mixed->maxRewardId->intval($rewards->get($i)->id);
        }
    }

    /**
     * @param integer $win
     * @param Player  $attacker
     * @param integer $attackerRank
     * @param Player  $defender
     * @param integer $defenderRank
     * @return void
     */
    public static function pvpDefendAspect($win, $attacker, $attackerRank, $defender, $defenderRank)
    {
        if ($defender->uid == 0) {
            return;
        }

        $message = new Message;
        if ($win) {
            if ($attackerRank > $defenderRank) {
                $tag = Message::TAG_PVP_RANKING_FALL;
            } else {
                $tag = Message::TAG_PVP_DEFEND_FAILURE;
            }
        } else {
            $tag = Message::TAG_PVP_DEFEND_SUCCESS;
        }

        $message->pid = $defender->id;
        $message->tag = $tag;
        switch ($tag) {
        case Message::TAG_PVP_DEFEND_FAILURE:
        case Message::TAG_PVP_DEFEND_SUCCESS:
            $message->addArgv($attacker->name);
            break;
        case Message::TAG_PVP_RANKING_FALL:
            $message->addArgv($attacker->name);
            $message->addArgv($attackerRank);
            break;
        }

        $message->save();
    }

    /**
     * @param integer $pid
     * @param string  $actid
     * @param integer $rank
     * @return void
     */
    public static function activityRankRewardAspect($pid, $actid, $rank)
    {
        $pr = new PlayerReward;
        $pr->pid = $pid;
        $pr->intval = $rank;
        $pr->strval = $actid;
        $pr->from = PlayerReward::from_actrank;
        $pr->save();
    }

    /**
     * @param integer $pid
     * @param string  $actid
     * @param integer $score
     * @return void
     */
    public static function activityScoreRewardAspect($pid, $actid, $score)
    {
        $pr = new PlayerReward;
        $pr->pid = $pid;
        $pr->intval = $score;
        $pr->strval = $actid;
        $pr->from = PlayerReward::from_actscore;
        $pr->save();
    }

    /**
     * @param integer $pid
     * @param string $rank
     * @return void
     */
    public static function actinsRewardAspect($pid, $rank)
    {
        $pr = new PlayerReward;
        $pr->pid = $pid;
        $pr->intval = $rank;
        $pr->from = PlayerReward::from_actins;
        $pr->save();
    }
}

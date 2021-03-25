<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes;

/**
 *
 */
use Exception;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Str,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\Object\Player\Reward as PlayerReward;

/**
 *
 */
class RewardMailInfo extends Box
{
    /**
     * @var array
     */
    protected static $lang = [
        PlayerReward::from_pvprank => [
            '竞技场排名奖励',
            '恭喜你在竞技场中排名%d, 特此奉上奖励!',
        ],

        PlayerReward::from_actrank => [
            '活动排名奖励',
            '恭喜你的活动中积分排名%d, 特此奉上奖励!',
        ],

        PlayerReward::from_actscore => [
            '活动积分奖励',
            '恭喜你在活动中累积积分达到%d, 特此奉上奖励!',
        ],
        
        PlayerReward::from_actins => [
            '活动副本排名奖励',
            '恭喜你在活动副本中排名%d, 特此奉上奖励!',
        ],

        PlayerReward::from_wdboss => [
            '世界BOSS奖励',
            '你对BOSS造成的伤害%d排名%d, 特此奉上奖励!',
        ],

        PlayerReward::from_bosskill => [
            '世界BOSS击杀',
            '恭喜你对BOSS造成致命一击, 特此表彰!',
        ],

        PlayerReward::from_top32 => [
            '32强争霸赛奖励',
            '你在32强争霸赛中取得第%d名,特此表彰!',
        ],
    ];

    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('id',   new U64);
        $this->attach('got',  new U16);

        $this->attach('time',   new U32);
        $this->attach('title',  new Str);
        $this->attach('content', new Str);

        $this->attach('reward', new RewardInfo);
    }

    /**
     * @param PlayerReward $pr
     * @return void
     */
    public function fromPlayerRewardObject(PlayerReward $pr, $global)
    {
        $this->id->intval($pr->id);
        $this->time->intval((integer)($pr->created / 1000000));
        if ($pr->from != PlayerReward::from_gm) {
            if (false === isset(self::$lang[$pr->from])) {
                throw new Exception('notfound pr tpl');
            }
            $lang = self::$lang[$pr->from];
            $this->title->strval($lang[0]);
            if ($pr->from == PlayerReward::from_bosskill) {
                $this->content->strval($lang[1]);
            } else if ($pr->from == PlayerReward::from_wdboss) {
                $this->content->strval(sprintf($lang[1],$pr->strval, $pr->intval));
            } else {
                $this->content->strval(sprintf($lang[1], $pr->intval));
            }
        } else {
            $this->title->strval($pr->profile->title);
            $this->content->strval($pr->profile->content);
        }
        $this->reward->fromArray($pr->getRewards(), $global);
    }
}

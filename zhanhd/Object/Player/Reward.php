<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Player;

/**
 *
 */
use System\Stdlib\PhpPdo,
    System\Object\DatabaseObject;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Object\Player;

/**
 *
 */
class Reward extends DatabaseObject
{
    /**
     * @var profile
     */
    public $profile= null;
    public $source = null;

    /**
     * @var integer
     */
    const from_pvprank  = 1;
    const from_actrank  = 2;
    const from_actscore = 3;
    const from_gm       = 4;
    const from_actins   = 5;
    const from_wdboss   = 6;
    const from_bosskill = 7;
    const from_top32    = 8;

    /**
     * @const integer
     */
    const FLAG_ACCEPTING = 1;
    const FLAG_ACCEPTED  = 2;

    /**
     * @return array
     */
    public function getRewards()
    {
        switch ($this->from) {
        case self::from_pvprank:
            return Store::get('reward', 1)->getRankRewards($this->intval);
        case self::from_actrank:
            if ($o = Store::get('reward', $this->strval)) {
                return $o->getRankRewards($this->intval);
            }
            return [];
        case self::from_actscore:
            if ($o = Store::get('reward', $this->strval)) {
                return $o->getScoreRewards($this->intval);
            }
            return [];
        case self::from_gm:
            return $this->source;
        case self::from_actins:
            return Store::get('reward', 2)->getRankRewards($this->intval);
        case self::from_wdboss:
            return Store::get('reward', 3)->getAllRewards($this->intval, $this->strval);
        case self::from_bosskill:
            return Store::get('worldboss', $this->intval)->drop();
        case self::from_top32:
            return Store::get('reward', 4)->getRankRewards($this->intval);
        }

        return [];
    }

    /**
     * @return array
     */
    public function getCoherences()
    {
        switch ($this->from) {
        case self::from_wdboss:
        case self::from_top32:
            return Store::get('reward', 4)->getRankCoherences($this->intval);
        }
        return [];
    }

    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerReward`';
    }

    /**
     *
     * @return array
     */
    public function columns()
    {
        return [
            'id'       => null,
            'pid'      => 0,
            'flags'    => 0,
            'created'  => 0,
            'sendtime' => 0,
            'expire'   => 0,
            'from'     => 0,
            'intval'   => 0,
            'strval'   => '',
        ];
    }

    /**
     *
     * @return array
     */
    public function primary()
    {
        return [
            'id' => null,
        ];
    }

    /**
     * @return void
     */
    protected function initial()
    {
        $this->profile = new Reward\Profile;
        $this->source  = new Reward\Source;
    }

    /**
     * @return void
     */
    protected function preInsert()
    {
        $this->flags = self::FLAG_ACCEPTING;
        $this->created = $this->ustime;
        if ($this->from != self::from_gm) {
            $this->expire = $this->created + 86400000000 * 15;
        }
    }

    /**
     *
     * @return void
     */
    protected function postInsert()
    {
        $this->id = $this->phppdo->lastInsertId();
        if ($this->from == self::from_gm) {
            $this->profile->setPlayerRewardId($this->id);
            $this->profile->save();
            $this->source->setPlayerRewardId($this->id);
            $this->source->save();
        }
    }

    /**
     * @return void
     */
    protected function postSelect()
    {
        if ($this->from == self::from_gm) {
            $this->profile->setPlayerRewardId($this->id);
            $this->profile->find();
            $this->source->setPlayerRewardId($this->id);
            $this->source->find();
        }
    }

    /**
     * @param PhpPdo $pdo
     * @param Player $p
     * @param integer $lastRewardId
     * @param integer $ustime
     * @return Object
     */
    public static function gets(PhpPdo $pdo, Player $p, $lastRewardId, $ustime)
    {
        $sql = 'SELECT * FROM `zhanhd.player`.`PlayerReward` WHERE (`pid` = ? OR `pid` = 0) AND `sendtime` < ? AND `expire` > ? AND `flags` = ? AND `id` > ? ORDER BY `id`';
        return self::buildSet($pdo, __CLASS__, $sql, [
            $p->id,
            $ustime,
            $ustime,
            self::FLAG_ACCEPTING,
            $lastRewardId,
        ], true);
    }

    /**
     * @param Player $p
     * @return void
     */
    public function rewardTo(Player $p)
    {
        if ($this->pid) {
            $this->flags = self::FLAG_ACCEPTED;
            $this->save();
        } else {
            $pra = new RewardAccepted;
            $pra->pid  = $p->id;
            $pra->prid = $this->id;
            $pra->save();
            $p->rewardAccepted->set($pra->prid, 1);
        }
    }
}

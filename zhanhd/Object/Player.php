<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object;

/**
 *
 */
use System\Stdlib\Object,
    System\Stdlib\PhpPdo,
    System\Object\DatabaseObject;

/**
 *
 */
use Zhanhd\Config\Entity   as SourceEntity;

/**
 *
 */
use Zhanhd\Object\Player\Entity       as PlayerEntity,
    Zhanhd\Object\Player\Lineup       as PlayerLineup,
    Zhanhd\Object\Player\Reward       as PlayerReward,
    Zhanhd\Object\Player\Crusade      as PlayerCrusade,
    Zhanhd\Object\Player\Building     as PlayerBuilding,
    Zhanhd\Object\Player\Illustration as PlayerIllustration,
    Zhanhd\Object\Player\Relation     as PlayerRelation,
    Zhanhd\Object\Guild\Member        as GuildMember,
    Zhanhd\Object\Guild\Pending       as GuildPending,
    Zhanhd\Extension\Identity,
    Zhanhd\Extension\TaskEmitter;

/**
 *
 */
class Player extends DatabaseObject
{
    /**
     * @const integer
     */
    const INVCODE_LENGTH = 10;

    /**
     * @var PlayerProfile
     */
    public $profile = null;

    /**
     * @var Counter
     */
    public $recent         = null,
           $counter        = null,
           $counterCycle   = null,
           $counterWeekly  = null,
           $counterMonthly = null,
           $rewardAccepted = null;

    /**
     * @param integer $zone
     * @param integer $uid
     * @return boolean
     */
    public function findByUid($zone, $uid)
    {
        return $this->findBySql(sprintf('SELECT * FROM %s WHERE `uid`=? and `zone`=? limit 1', $this->schema()), [
            $uid,
            $zone,
        ]);
    }

    /**
     * @param integer $invcode
     * @return boolean
     */
    public function findByInvcode($invcode)
    {
        return $this->findBySql(sprintf('SELECT * FROM %s WHERE `invcode`=? limit 1', $this->schema()), [
            $invcode,
        ]);
    }

    /**
     * @return boolean
     */
    public function isMember()
    {
        if ($this->profile->monthlyCardExpire < $this->ustime) {
            return false;
        }

        return true;
    }

    /**
     * @return boolean
     */
    public function isInvited()
    {
        return Player\Invite::beInvited($this->phppdo, $this);
    }

    /**
     * @return integer secord time
     */
    public function memcardRemain()
    {
        $remain = $this->profile->monthlyCardExpire - $this->ustime;
        if ($remain > 0) {
            return (integer) ($remain / 1000000);
        }

        return 0;
    }

    /**
     * @param integer $lastMessageId
     * @return Object
     */
    public function getMessages($lastMessageId)
    {
        return Message::gets($this->phppdo, $this, $lastMessageId, $this->ustime);
    }

    /**
     * @param integer $page
     * @param integer $num
     * @return Object
     */
    public function getMessagePage($page, $num)
    {
        return Message::getPage($this->phppdo, $this, $page, $num, $this->ustime);
    }

    /**
     * @return integer
     */
    public function getMessageTotal()
    {
        return Message::getTotal($this->phppdo, $this, $this->ustime);
    }

    /**
     * @param integer $lastRewardId
     * @return Object
     */
    public function getRewards($lastRewardId)
    {
        $all = Player\Reward::gets($this->phppdo, $this, $lastRewardId, $this->ustime);
        $filter = new Object;
        foreach ($all as $k => $o) {
            if ($o->pid == 0 && $this->rewardAccepted->{$o->id}) {
                continue;
            }
            $filter->set(null, $o);
        }
        return $filter;
    }

    /**
     *
     * @return Object
     */
    public function getBuildings()
    {
        return PlayerBuilding::gets($this->phppdo, $this)->filter(function($pb) {
            return $pb->b->enable;
        }, true);
    }

    /**
     *
     * @param  integer $bid
     * @return PlayerBuilding
     */
    public function getBuilding($bid)
    {
        $pb = new PlayerBuilding;
        if ($pb->find($this->id, $bid)) {
            return $pb;
        }
    }

    /**
     * @return Object
     */
    public function getLineups()
    {
        return PlayerLineup::gets($this->phppdo, $this, 'gid');
    }

    /**
     * @param integer $gid
     * @return Object
     */
    public function getLineup($gid)
    {
        $pl = new PlayerLineup;
        if ($pl->find($this->id, $gid)) {
            return $pl;
        }
        return null;
    }

    /**
     *
     * @return Object
     */
    public function getCrusades()
    {
        return PlayerCrusade::gets($this->phppdo, $this);
    }

    /**
     *
     * @return Object
     */
    public function getEntities()
    {
        return PlayerEntity::gets($this->phppdo, $this);
    }

    /**
     * @return Object
     */
    public function getRefine()
    {
        return Player\Entity\Refine::gets($this->phppdo, $this->id);
    }

    /**
     * @return integer
     */
    public function getGuildMember()
    {
        $o = new GuildMember;
        $o->player = $this;
        if (false === $o->findByPid($this->id)) {
            return null;
        }
        return $o;
    }

    /**
     * @return Object
     */
    public function getGuildPending()
    {
        return GuildPending::getsByPid($this->phppdo, $this->id);
    }

    /**
     * @return array
     */
    public function freePackageCapacity()
    {
        $used = [
            'hero'  => 0,
            'equip' => 0,
        ];
        foreach (PlayerEntity::gets($this->phppdo, $this) as $o) {
            if ($o->e->type == SourceEntity::TYPE_HERO) {
                $used['hero']++;
            } else {
                $used['equip']++;
            }
        }
        return [
            'hero'  => $this->profile->heroPackageCapacity - $used['hero'],
            'equip' => $this->profile->equipPackageCapacity - $used['equip'],
        ];
    }

    /**
     *
     * @return Object
     */
    public function getIllustration()
    {
        return PlayerIllustration::gets($this->phppdo, $this);
    }

    /**
     * @return Object
     */
    public function getRelationShip()
    {
        return PlayerRelation::gets($this->phppdo, $this);
    }

    /**
     * @return Object
     */
    public function getFriends()
    {
        return PlayerRelation::getsByFlags($this->phppdo, $this, PlayerRelation::FLAG_FRIEND);
    }

    /**
     * @return Object
     */
    public function getRelationConfirms()
    {
        return PlayerRelation::getsByFlags($this->phppdo, $this, PlayerRelation::FLAG_CONFIRM);
    }

    /**
     * @return void
     */
    public function getBlacklist()
    {
        return PlayerRelation::getsByFlags($this->phppdo, $this, PlayerRelation::FLAG_BLACK);
    }

    /**
     * @return PlayerRelation
     */
    public function getRelation($fid)
    {
        $pr = new PlayerRelation;
        if ($pr->findByPair($this->id, $fid)) {
            return $pr;
        }

        return null;
    }

    /**
     * @param integer $incr
     * @return void
     */
    public function incrGold($incr)
    {
        $this->gold += $incr;
        $this->counter->diamondGain += $incr;
        $this->counterCycle->diamondGain += $incr;
    }

    /**
     * @param integer $decr
     * @return void
     */
    public function decrGold($decr)
    {
        $this->gold -= $decr;
        $this->counterWeekly->diamondConsume += $decr;
        TaskEmitter::consumeGold($this, $this->retrieveScope('globals'));
    }

    /**
     *
     * @param  Object   $what
     * @param  callable $callback
     * @param  array    $argv
     * @return void
     */
    public function increaseEntity(Object $what, callable $callback = null, ... $argv)
    {
        $global = $this->retrieveScope('globals');
        foreach ($what as $eid => $o) {
            if ($o->e->stackable) {
                if ($o->e->type == SourceEntity::TYPE_MONEY) {
                    $this->incrGold($o->n);
                } else {
                    $this->profile->$eid += $o->n;
                }
                if ($callback) {
                    $callback($o, ...$argv);
                }
            } else {
                for ($i = 0; $i < $o->n; $i++) {
                    $pe = new PlayerEntity;
                    $pe->e = $o->e;
                    $pe->pid = $this->id;
                    $pe->eid = $o->e->id;
                    $pe->save();

                    $pi = new PlayerIllustration;
                    if (false === $pi->find($this->id, $o->e->id)) {
                        $pi->pid  = $this->id;
                        $pi->eid  = $o->e->id;
                        $pi->type = $o->e->type;
                        $pi->save();
                    }

                    if ($callback) {
                        $callback($pe, ... $argv);
                    }
                }
            }
            TaskEmitter::increaseEntity($this, $global, $o);
        }
        $this->save();
    }

    /**
     * @param  integer $zone
     * @param  string  $name
     * @return mixed
     */
    public function nameExists($zone, $name)
    {
        $stmt = $this->phppdo->prepare(sprintf('SELECT `id` FROM %s WHERE `zone` = ? AND `name` = ?', $this->schema()));
        $stmt->execute(array($zone, $name));

        return $stmt->fetchColumn();
    }

    /**
     * @param PhpPdo $pdo
     * @param integer $name
     * @return array
     */
    public static function nameSearch(PhpPdo $pdo, $name)
    {
        $name = strtoupper(addslashes($name));
        $stmt = $pdo->prepare(
            "SELECT p.`id` as id FROM `zhanhd.player`.`Player` p LEFT JOIN `zhanhd.global`.`User` u on (p.uid=u.id) WHERE p.uid!=0 AND u.`flags`=".(User::FLAG_NORMAL)." AND UPPER(`name`) LIKE '%$name%'"
        );
        $stmt->execute();
        $ids = [];
        while ($o = $stmt->fetchObject()) {
            $ids[$o->id] = $o->id;
        }
        return $ids;
    }

    /**
     * @param PhpPdo $pdo
     * @return array
     */
    public static function recommendFriendIds(PhpPdo $pdo)
    {
        $stmt = $pdo->query('SELECT `id` FROM `zhanhd.player`.`Player` WHERE `uid`!=0 ORDER BY `lastLogin` DESC LIMIT 30');
        $ids = [];
        while ($o = $stmt->fetchObject()) {
            $ids[$o->id] = $o->id;
        }
        return $ids;
    }

    /**
     * @param PhpPdo $pdo
     * @param integer $count
     * @param array  $filter
     * @return array
     */
    public static function randomIds(PhpPdo $pdo, $count, $filter = [])
    {
        $stmt = $pdo->query('select max(id) from `zhanhd.player`.`Player` where `uid`=0');
        $minId = (int)$stmt->fetchColumn(0)+1;
        $stmt = $pdo->query('select max(id) from `zhanhd.player`.`Player`');
        $maxId = (int)$stmt->fetchColumn(0);

        $where = implode(' and ', $filter);
        if ($where) {
            $sql1 = sprintf('select id from `zhanhd.player`.`Player` where id > ? and id <= ? and %s limit %d', $where, $count*5);
        } else {
            $sql1 = sprintf('select id from `zhanhd.player`.`Player` where id > ? and id <= ? limit %d', $count*5);
        }
        if ($where) {
            $sql2 = sprintf('select id from `zhanhd.player`.`Player` where id <= ? and id >= ? and %s limit %d', $where, $count*5);
        } else {
            $sql2 = sprintf('select id from `zhanhd.player`.`Player` where id <= ? and id >= ? limit %d', $count*5);
        }
        $seed = mt_rand($minId, $maxId);
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([$seed, $maxId]);
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([$seed, $minId]);
        $ids = [];
        while ($id = $stmt1->fetchColumn(0)) $ids[] = $id;
        while ($id = $stmt2->fetchColumn(0)) $ids[] = $id;
        shuffle($ids);
        return array_slice($ids, 0, $count);
    }

    /**
     * @return array
     */
    public function robResource()
    {
        $TAB = [
            3 => 2,
            4 => 3,
            5 => 4,
            8 => 6,
            9 => 7,
        ];
        $rewards = [];
        $buildings = $this->getBuildings();
        foreach ($buildings as $o) {
            if (isset($TAB[$o->bid]) === false) continue;
            $rewards[$TAB[$o->bid]] = $o->lvl*10;
        }
        return $rewards;
    }

    /**
     * @param PhpPdo $pdo
     * @param integer $ustime
     * @return Object
     */
    public static function lastLoginBefore(PhpPdo $pdo, $ustime, $start, $length)
    {
        $sql = sprintf('select * from `zhanhd.player`.`Player` where `uid`!=0 and `lastLogin`<? limit %d,%d', $start, $length);
        return self::buildSet($pdo, __CLASS__, $sql, [
            $ustime,
        ], true);
    }

    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`Player`';
    }

    /**
     *
     * @return array
     */
    public function columns()
    {
        return [
            'id'        => 0,
            'uid'       => 0,
            'zone'      => 0,
            'name'      => '',
            'gold'      => 0,
            'deposit'   => 0,
            'created'   => 0,
            'lastLogin' => 0,
            'logout'    => 0,
            'invcode'   => '',
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
     *
     * @return void
     */
    protected function initial()
    {
        $this->profile = new Player\Profile;

        $this->recent         = new Player\Recent;
        $this->counter        = new Player\Counter;
        $this->counterCycle   = new Player\Counter\Cycle;
        $this->counterWeekly  = new Player\Counter\Weekly;
        $this->counterMonthly = new Player\Counter\Monthly;
    }

    /**
     *
     * @return void
     */
    protected function postSelect()
    {
        $this->profile->setPlayerId($this->id);
        $this->profile->find();

        $this->recent->setPlayerId($this->id);
        $this->recent->find();

        $this->counter->setPlayerId($this->id);
        $this->counter->find();

        $globals = $this->retrieveScope('globals');
        $this->counterCycle->setPlayerId($this->id);
        $this->counterCycle->setCycle($globals->date);
        $this->counterCycle->find();

        $this->counterWeekly->setPlayerId($this->id);
        $this->counterWeekly->setWeek($globals->week);
        $this->counterWeekly->find();

        $this->counterMonthly->setPlayerId($this->id);
        $this->counterMonthly->setMonth($globals->month);
        $this->counterMonthly->find();

        $this->rewardAccepted = Player\RewardAccepted::gets($this->phppdo, $this->id);
    }

    /**
     * @string alpnum
     */
    protected static $alpnum = 'abcdefghijklmnopqrstuvwxyz0123456789';

    /**
     * return string
     */
    public static function generateInvcode()
    {
        $code = '';
        for ($i = 0; $i < self::INVCODE_LENGTH; $i++) {
            $code .= self::$alpnum[mt_rand(0, 35)];
        }
        return $code;
    }

    /**
     * @param PhpPdo $pdo
     * @param string $invcode
     * @return boolean
     */
    public static function invcodeExists(PhpPdo $pdo, $invcode)
    {
        $stmt = $pdo->prepare('SELECT COUNT(1) FROM `zhanhd.player`.`Player` WHERE `invcode`=? LIMIT 1');
        $stmt->execute([$invcode]);
        return (boolean)$stmt->fetchColumn(0);
    }

    /**
     *
     * @return void
     */
    protected function preInsert()
    {
        $this->id = Identity::generatePId($this->retrieveScope('globals')->redis);
        $this->created = $this->ustime;
    }

    /**
     * @return void
     */
    protected function postInsert()
    {
        $this->recent->setPlayerId($this->id);

        $this->profile->setPlayerId($this->id);

        $this->counter->setPlayerId($this->id);

        $globals = $this->retrieveScope('globals');
        $this->counterCycle->setPlayerId($this->id);
        $this->counterCycle->setCycle($globals->date);

        $this->counterWeekly->setPlayerId($this->id);
        $this->counterWeekly->setWeek($globals->week);

        $this->counterMonthly->setPlayerId($this->id);
        $this->counterMonthly->setMonth($globals->month);

        $this->rewardAccepted = Player\RewardAccepted::gets($this->phppdo, $this->id);
    }

    /**
     *
     * @return void
     */
    protected function postUpdate()
    {
        $this->profile->save();

        $this->recent      ->save();
        $this->counter     ->save();
        $this->counterCycle->save();
        $this->counterWeekly->save();
        $this->counterMonthly->save();
    }

    /**
     *
     * @return void
     */
    protected function preDelete()
    {
        $this->profile->drop();

        $this->recent      ->drop();
        $this->counter     ->drop();
        $this->counterCycle->drop();
        $this->counterWeekly->drop();
        $this->counterMonthly->drop();
    }

    /**
     * @return boolean
     */
    public function userValidateStatus()
    {
        $u = new User;
        $u->find($this->uid);
        return $u->validateStatus();
    }

    /**
     * @return User
     */
    public function getUser()
    {
        $u = new User;
        $u->find($this->uid);
        return $u;
    }
}

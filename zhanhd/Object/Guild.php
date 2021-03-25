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
use System\Stdlib\PhpPdo,
    System\Object\DatabaseObject;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Config\Guild\Exp as GuildExp,
    Zhanhd\Object\Message;

/**
 *
 */
class Guild extends DatabaseObject
{
    /**
     * @const integer
     */
    const PRICE      = 500;
    const IMPEACHNUM = 5;

    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`Guild`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'id'      => 0,
            'name'    => '',
            'lvl'     => 0,
            'exp'     => 0,
            'memnum'  => 0,
            'pending' => 0,
            'bulletin'=> '',
            'founder' => 0,
            'created' => 0,
            'lastjoin'=> 0,
        ];
    }

    /**
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
    protected function preInsert()
    {
        $this->created = $this->ustime;
    }

    /**
     * @return void
     */
    protected function postInsert()
    {
        $this->id = $this->phppdo->lastInsertId();
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function findByName($name)
    {
        return $this->findBySql('SELECT * FROM `zhanhd.player`.`Guild` WHERE `name`=?', [ $name, ]);
    }

    /**
     * @param PhpPdo $pdo
     * @param array  $filter
     * @return Object
     */
    public static function recommendGuild(PhpPdo $pdo, array $filter)
    {
        if (empty($filter)) {
            $sql = 'SELECT * FROM `zhanhd.player`.`Guild` WHERE `pending`+3*`memnum` < 3*(5*`lvl`+5) ORDER BY `lastjoin` DESC LIMIT 30';
        } else {
            $filter = implode(',', $filter);
            $sql = 'SELECT * FROM `zhanhd.player`.`Guild` WHERE `pending`+3*`memnum` < 3*(5*`lvl`+5) AND `id` not in ('.$filter.') ORDER BY `lastjoin` DESC LIMIT 30';
        }
        return self::buildSet($pdo, __CLASS__, $sql);
    }

    /**
     * @param PhpPdo $pdo
     * @param string $key
     * @return Object
     */
    public static function keywordSearch(PhpPdo $pdo, $key, array $filter)
    {
        if (empty($filter)) {
            $sql = "SELECT * FROM `zhanhd.player`.`Guild` WHERE `name` LIKE '%$key%'";
        } else {
            $filter = implode(',', $filter);
            $sql = "SELECT * FROM `zhanhd.player`.`Guild` WHERE `name` LIKE '%$key%' AND `id` not in (".$filter.")";
        }
        return self::buildSet($pdo, __CLASS__, $sql);
    }

    /**
     * @return Object
     */
    public function getMembers()
    {
        return Guild\Member::gets($this->phppdo, $this);
    }

    /**
     * @param Player $p
     * @param integer $post
     * @return Guild\Member
     */
    public function addMember(Player $p, $post)
    {
        $member = new Guild\Member;
        $member->player = $p;
        $member->gid = $this->id;
        $member->pid = $p->id;
        $member->post = $post;
        $member->save();
        $g = $this->retrieveScope('globals');
        $this->lastjoin = $g->ustime;
        $this->memnum++;
        $this->save();
        return $member;
    }

    /**
     * @param Guild\Member $guildMember
     * @return void
     */
    public function removeMember(Guild\Member $guildMember)
    {
        $guildMember->drop();
        $this->memnum--;
        $this->save();
        $g = $this->retrieveScope('globals');
        $guildMember->player->recent->leaveGuild = $g->ustime;
        $guildMember->player->recent->save();
    }

    /**
     * @return Object
     */
    public function getPendings()
    {
        return Guild\Pending::getsByGid($this->phppdo, $this->id);
    }

    /**
     * @param Player $p
     * @return Guild\Pending
     */
    public function addPending(Player $p)
    {
        $pending = new Guild\Pending;
        if ($pending->find($this->id, $p->id)) {
            return null;
        }

        $pending->gid = $this->id;
        $pending->pid = $p->id;
        $pending->save();

        $this->pending++;
        $this->save();
        return $pending;
    }

    /**
     * @param Guild\Pending $pending
     * @return void
     */
    public function removePending($pending)
    {
        $pending->drop();
        $this->pending--;
        $this->save();
    }

    /**
     * @param array $pendings
     * @return void
     */
    public function removePendings(array $pendings)
    {
        foreach ($pendings as $pending) {
            $pending->drop();
        }
        $this->pending -= count($pendings);
        $this->save();
    }

    /**
     * @return Guild\Member
     */
    public function getPresident()
    {
        $member = new Guild\Member;
        if ($member->findPresident($this)) {
            return $member;
        }
        return null;
    }

    /**
     * @return Guild\Member
     */
    public function getViceChairman()
    {
        $member = new Guild\Member;
        if ($member->findViceChairman($this)) {
            return $member;
        }
        return null;
    }

    /**
     * @param integer $pid
     * @return Guild\Member
     */
    public function getMember($pid)
    {
        $member = new Guild\Member;
        if ($member->find($this->id, $pid)) {
            return $member;
        }
        return null;
    }

    /**
     * @return Guild\Impeach
     */
    public function getImpeach()
    {
        $impeach = new Guild\Impeach;
        if (false === $impeach->find($this->id)) return null;
        $g = $this->retrieveScope('globals');
        if ($g->ustime - $impeach->time > 48*3600*1000000) {
            $impeach->drop();
            $m = new Message;
            $m->gid = $this->id;
            $m->tag = Message::TAG_GUILD_IMPEACHFAIL;
            $m->save();
            return null;
        }
        return $impeach;
    }

    /**
     * @return integer
     */
    public function getMemberLimit()
    {
        return 5*$this->lvl + 5;
    }

    /**
     * @return integer
     */
    public function getCurrExp()
    {
        return $this->exp-Store::get('guildexp', $this->lvl)->exp;
    }

    /**
     * @param integer $contribution
     * @return boolean
     */
    public function contribute($contribution)
    {
        $explimit = Store::get('guildexp', GuildExp::MAXLVL)->exp;
        if ($this->exp == $explimit) {
            return;
        }
        $stmt = $this->phppdo->prepare(sprintf('UPDATE %s SET `exp`=`exp`+? WHERE `id`=?', $this->schema()));
        $stmt->execute([
            $contribution, $this->id,
        ]);
        $stmt = $this->phppdo->prepare(sprintf('SELECT `exp` from %s WHERE `id`=?', $this->schema()));
        $stmt->execute([$this->id]);
        $ret = $stmt->fetchColumn(0);
        if ($ret > $explimit) {
            $stmt = $this->phppdo->prepare(sprintf('UPDATE %s SET `exp`=? WHERE `id`=?', $this->schema()));
            $stmt->execute([
                $explimit, $this->id,
            ]);
            $ret = $explimit;
        }

        $upgraded = false;
        for ($nextlvl = $this->lvl+1; $nextlvl <= GuildExp::MAXLVL; $nextlvl++) {
            $o = Store::get('guildexp', $nextlvl);
            if ($ret >= $o->exp) {
                $upgraded = true;
            } else {
                break;
            }
        }
        if ($upgraded) {
            $currlvl = $nextlvl-1;
            $stmt = $this->phppdo->prepare(sprintf('UPDATE %s SET `lvl`=? WHERE `id`=?', $this->schema()));
            $stmt->execute([
                $currlvl, $this->id,
            ]);
            $this->daemonSet('lvl', $currlvl);
        }
        $this->daemonSet('exp', $ret);
    }

    /**
     * @return Guild\Member
     */
    public function selectNewPresident()
    {
        $president = new Guild\Member;
        if (false === $president->selectNewPresident($this->id)) {
            return null;
        }
        return $president;
    }

    /**
     *
     * @return int
     */
    public function getRankScore()
    {
        $power = 0;
        foreach (Guild\Member::gets($this->phppdo, $this, true) as $m) {
            $power += $m->player->getLineup(1)->power;
        }
        return $this->lvl*1000000000000 + $power;
    }

    /**
     *
     * @param int $score
     * @return array
     */
    public function parseRankScore($score)
    {
        $level = (int)($score/1000000000000);
        $power = $score%1000000000000;
        return array($level, $power);
    }
}

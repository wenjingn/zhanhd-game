<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Guild;

/**
 *
 */
use System\Stdlib\PhpPdo,
    System\Stdlib\Object,
    System\Object\DatabaseObject;

/**
 *
 */
use Zhanhd\Object\Player,
    Zhanhd\Object\Guild;

/**
 *
 */
class Member extends DatabaseObject
{
    /**
     * @const integer
     */
    const POST_PRESIDENT    = 1;
    const POST_VICECHAIRMAN = 2;
    const POST_OLDDRIVER    = 3;
    const POST_GREENER      = 4;

    /**
     * @var Player
     */
    public $player = null;
    public $daily  = null;

    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`GuildMember`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'gid'  => 0,
            'pid'  => 0,
            'post' => 0,
            'cont' => 0,
            'contused' => 0,
            'join' => 0,
        ];
    }

    /**
     * @return array
     */
    public function primary()
    {
        return [
            'gid' => null,
            'pid' => null,
        ];
    }

    /**
     * @return void
     */
    protected function initial()
    {
        $this->daily = new Member\Daily;
    }

    /**
     * @return void
     */
    protected function preInsert()
    {
        $this->join = $this->ustime;
    }

    /**
     * @return void
     */
    protected function postInsert()
    {
        $this->daily->setGuildId($this->gid);
        $this->daily->setPlayerId($this->pid);
        $g = $this->retrieveScope('globals');
        $this->daily->setDate($g->date);
        $this->daily->save();
    }

    /**
     * @return void
     */
    protected function postUpdate()
    {
        $this->daily->save();
    }

    /**
     * @return void
     */
    protected function postSelect()
    {
        if ($this->player === null) {
            $this->player = new Player;
            $this->player->find($this->pid);
        }
        $this->daily->setGuildId($this->gid);
        $this->daily->setPlayerId($this->pid);
        $g = $this->retrieveScope('globals');
        $this->daily->setDate($g->date);
        $this->daily->find();
    }

    /**
     * @return void
     */
    protected function preDelete()
    {
        $this->daily->drop();
    }

    /**
     * @param integer $pid
     * @return Member
     */
    public function findByPid($pid)
    {
        return $this->findBySql('SELECT * FROM `zhanhd.player`.`GuildMember` WHERE `pid`=? LIMIT 1', [$pid]);
    }

    /**
     * @return boolean
     */
    public function isPresident()
    {
        return $this->post == self::POST_PRESIDENT;
    }

    /**
     * @return boolean
     */
    public function isViceChairman()
    {
        return $this->post == self::POST_VICECHAIRMAN;
    }

    /**
     * @return boolean
     */
    public function isGuildManager()
    {
        return $this->post == self::POST_PRESIDENT || $this->post == self::POST_VICECHAIRMAN;
    }

    /**
     * @return integer
     */
    public function getPost()
    {
        if ($this->post == self::POST_GREENER) {
            $g = $this->retrieveScope('globals');
            if ($g->ustime - $this->join > 2*86400*1000000) {
                $this->post = self::POST_OLDDRIVER;
                $this->save();
            }
        }
        return $this->post;
    }

    /**
     * @return Guild
     */
    public function getGuild()
    {
        $o = new Guild;
        $o->find($this->gid);
        return $o;
    }

    /**
     * @param PhpPdo $pdo
     * @param Guild $g
     * @param bool $postSelect
     * @return Object
     */
    public static function gets(PhpPdo $pdo, $g, $postSelect = false)
    {
        return self::buildSet($pdo, __CLASS__, 'SELECT * FROM `zhanhd.player`.`GuildMember` WHERE `gid`=? order by `post`', [ $g->id ], $postSelect);
    }

    /**
     * @param PhpPdo  $pdo
     * @param integer $gid
     * @param integer $idx
     * @param integer $size
     * @return Object
     */
    public static function getPage(PhpPdo $pdo, $gid, $idx, $size = 10)
    {
        $page = new Object;
        $stmt = $pdo->prepare('SELECT COUNT(1) FROM `zhanhd.player`.`GuildMember` WHERE `gid`=?');
        $stmt->execute([$gid]);
        $count = (int)$stmt->fetchColumn(0);
        $total = ceil($count/$size);
        if ($idx < 1 || $idx > $total) {
            $data = [];
        } else {
            $sql = sprintf('SELECT * FROM `zhanhd.player`.`GuildMember` WHERE `gid`=? order by `post` LIMIT %d,%d', ($idx-1)*10, $size);
            $data = self::buildSet($pdo, __CLASS__, $sql, [ $gid ], true);
        }
        $page->index = $idx;
        $page->total = $total;
        $page->data  = $data;
        return $page;
    }

    /**
     * @param Guild $g
     * @return boolean
     */
    public function findPresident($g)
    {
        return $this->findBySql('SELECT * FROM `zhanhd.player`.`GuildMember` WHERE `gid`=? AND `post`=? LIMIT 1', [ $g->id, self::POST_PRESIDENT, ]);
    }

    /**
     * @param Guild $g
     * @return boolean
     */
    public function findViceChairman($g)
    {
        return $this->findBySql('SELECT * FROM `zhanhd.player`.`GuildMember` WHERE `gid`=? AND `post`=? LIMIT 1', [ $g->id, self::POST_VICECHAIRMAN, ]);
    }

    /**
     * @param integer $gid
     * @return boolean
     */
    public function selectNewPresident($gid)
    {
        $sql = 'select gm.* from `zhanhd.player`.`GuildMember` gm left join `zhanhd.player`.`GuildMemberDaily` gmd on (gm.gid=gmd.gid and gm.pid=gmd.pid)';
        $sql.= ' where gm.gid=? and gmd.date=? and gmd.k=? order by gmd.v desc, gm.`join` limit 1';
        $g = $this->retrieveScope('globals');
        return $this->findBySql($sql, [
            $gid, $g->date, 'contribution',
        ]);
    }
}

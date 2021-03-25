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
use System\Object\DatabaseObject,
    System\Stdlib\Object,
    System\Stdlib\PhpPdo;

/**
 *
 */
use Zhanhd\Config\Store;

/**
 *
 */
class Message extends DatabaseObject
{
    /**
     * @const integer
     */
    const TAG_SYSTEM_MESSAGE     = 0;

    const TAG_PVP_DEFEND_FAILURE = 1;
    const TAG_PVP_RANKING_FALL   = 2;
    const TAG_PVP_DEFEND_SUCCESS = 3;
    const TAG_GUILD_JOIN         = 4;
    const TAG_GUILD_TRANSFER     = 5;
    const TAG_GUILD_APPOINT      = 6;
    const TAG_GUILD_IMPEACHSTART = 7;
    const TAG_GUILD_IMPEACHSUCC  = 8;
    const TAG_GUILD_IMPEACHFAIL  = 9;
    const TAG_TOP32_SELECTED     = 10;

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`Message`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'id'      => 0,
            'pid'     => 0,
            'gid'     => 0,
            'tag'     => 0,
            'argvs'   => '',
            'created' => 0,
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
        $this->argvs = implode(',', $this->arguments);
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
     * @return void
     */
    protected function postSelect()
    {
        $this->arguments = explode(',', $this->argvs);
    }

    /**
     * @sequence is important
     *
     * @param mixed $argv
     * @return void
     */
    public function addArgv($argv)
    {
        $this->arguments[] = $argv;
    }

    /**
     * @return array
     */
    public function getArgvs()
    {
        return $this->arguments;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        if ($this->tag == self::TAG_SYSTEM_MESSAGE) {
            return '系统消息';
        }
        if (false === Store::has('messageTemplate', $this->tag)) {
            return '';
        }
        return Store::get('messageTemplate', $this->tag)->title;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        if ($this->tag == self::TAG_SYSTEM_MESSAGE) {
            return $this->argvs;
        }
        if (false === Store::has('messageTemplate', $this->tag)) {
            return '';
        }
        return sprintf(Store::get('messageTemplate', $this->tag)->content, ... $this->arguments);
    }

    /**
     * @param PhpPdo $pdo
     * @param Player $p
     * @param integer $lastMessageId
     * @param integer $ustime
     * @return Object
     */
    public static function gets(PhpPdo $pdo, Player $p, $lastMessageId, $ustime)
    {
        $guildMember = $p->getGuildMember();
        if (null === $guildMember) {
            $sql = 'SELECT * FROM `zhanhd.player`.`Message` WHERE (`pid` = ? or (`pid` = 0 and `gid` = 0)) AND `created` > ? AND `id` > ?';
            return self::buildSet($pdo, __CLASS__, $sql, [
                $p->id,
                max($p->created, $ustime - 86400000000 * 15),
                $lastMessageId,
            ], true);
        }
        
        $sql = 'SELECT * FROM `zhanhd.player`.`Message` WHERE (`pid` = ? or (`pid` = 0 and `gid` = 0) or `gid` = ?) AND `created` > ? AND `id` > ?';
        return self::buildSet($pdo, __CLASS__, $sql, [
            $p->id,
            $guildMember->gid,
            max($p->created, $ustime - 86400000000 * 15),
            $lastMessageId,
        ], true);
    }

    /**
     * @param PhpPdo $pdo
     * @param Player $p
     * @param integer $idx
     * @param integer $num
     * @param integer $ustime
     * @return Object
     */
    public static function getPage(PhpPdo $pdo, Player $p, $idx, $num, $ustime)
    {
        $page = new Object;
        $guildMember = $p->getGuildMember();
        if (null === $guildMember) {
            $sql = 'SELECT COUNT(1) FROM `zhanhd.player`.`Message` WHERE (`pid`=? or (`pid`=0 and `gid`=0)) AND `created` > ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $p->id, max($p->created, $ustime - 86400000000*15),        
            ]);
            $total = $stmt->fetchColumn(0);

            if ($idx == 0 || $num*($idx-1) >= $total) {
                $data = [];
            } else {
                $sql = sprintf(
                    'SELECT * FROM `zhanhd.player`.`Message` WHERE (`pid`=? or (`pid`=0 and `gid`=0)) AND `created`>? ORDER BY `id` DESC LIMIT %d,%d',
                    $num*($idx-1),
                    $num
                );
                $data = self::buildSet($pdo, __CLASS__, $sql, [
                    $p->id,
                    max($p->created, $ustime - 86400000000*15),
                ], true);
            }
        } else {
            $sql = 'SELECT COUNT(1) FROM `zhanhd.player`.`Message` WHERE (`pid`=? or (`pid`=0 and `gid`=0) or `gid`=?) AND `created` > ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $p->id, $guildMember->gid, max($p->created, $ustime - 86400000000*15),
            ]);
            $total = $stmt->fetchColumn(0);

            if ($idx == 0 || $num*($idx-1) >= $total) {
                $data = [];
            } else {
                $sql = sprintf(
                    'SELECT * FROM `zhanhd.player`.`Message` WHERE (`pid`=? or (`pid`=0 and `gid`=0) or `gid`=?) AND `created`>? ORDER BY `id` DESC LIMIT %d,%d',
                    $num*($idx-1),
                    $num
                );
                $data = self::buildSet($pdo, __CLASS__, $sql, [
                    $p->id,
                    $guildMember->gid,
                    max($p->created, $ustime - 86400000000*15),
                ], true);
            }
        }
        $page->index = $idx;
        $page->total = $total;
        $page->data  = $data;
        return $page;
    }
}

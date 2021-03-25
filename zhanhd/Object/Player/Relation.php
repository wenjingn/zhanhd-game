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
use Zhanhd\Object\Player;

/**
 *
 */
class Relation extends DatabaseObject
{
    /**
     * @const integer
     */
    const MAX_LIMIT = 30;

    /**
     * @const integer
     */
    const FLAG_WAITING = 1;
    const FLAG_CONFIRM = 2;
    const FLAG_FRIEND  = 4;
    const FLAG_BLACK   = 8;
    const FLAG_MASK    = 15;

    /**
     * @const interge
     */
    const LIKE_ENERGY = 10;

    /**
     * @const integer
     */
    const LOVE_FULL = 55;

    /**
     * @const integer
     */
    const GAIN_FREE = 5;
    const GAIN_PAID = 15;

    /**
     * @const integer
     */
    const PAID_CARDINAL = 10;

    /**
     * @var array
     */
    public static $gears = [
        1 => 25,
        2 => 35,
        3 => 45,
        4 => 55,
    ];

    /**
     * @var array
     */
    public static $picks = [
        1 => [
            303001 => 2500,
            303006 => 2500,
            303011 => 2500,
            303016 => 2500,
        ],

        2 => [
            303001 => 2400,
            303006 => 2400,
            303011 => 2400,
            303016 => 2400,
            303002 => 400,
        ],

        3 => [
            303002 => 2400,
            303007 => 2400,
            303012 => 2400,
            303017 => 2400,
            303003 => 200,
            303004 => 200,
        ],

        4 => [
            303003 => 2200,
            303008 => 2200,
            303013 => 2200,
            303018 => 2200,
            303019 => 800,
            303014 => 700,
            303005 => 500,
        ],
    ];

    /**
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerRelation`';
    }

    /**
     * @return array
     */
    public function columns()
    {
        return [
            'id'        => 0,
            'pid'       => 0,
            'fid'       => 0,
            'flags'     => 0,
            'likeTimes' => 0,
            'lastLiked' => 0,
            'loveValue' => 0,
            'created'   => 0,
            'updated'   => 0,
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
     * @param integer $flags
     * @return boolean
     */
    public function hasflags($flags)
    {
        return (boolean)($this->flags&$flags);
    }

    /**
     * @param integer $flags
     * @return void
     */
    public function addflags($flags)
    {
        $this->flags |= $flags;
    }

    /**
     * @param integer $flags
     * @return void
     */
    public function remflags($flags)
    {
        $this->flags &= ~$flags;
    }

    /**
     * @return boolean
     */
    public function isFriend()
    {
        return (boolean)($this->flags&self::FLAG_FRIEND);
    }

    /**
     * @param integer $pid
     * @param integer $fid
     * @return Object
     */
    public function findByPair($pid, $fid)
    {
        return $this->findBySql(sprintf('SELECT * FROM %s WHERE `pid`=? and `fid`=?', $this->schema()), [$pid, $fid]);
    }

    /**
     * @param PhpPdo $pdo
     * @param Player $p
     * @return Object
     */
    public static function gets(PhpPdo $pdo, Player $p)
    {
        return self::buildSet($pdo, __CLASS__, 
            'SELECT * FROM `zhanhd.player`.`PlayerRelation` WHERE `pid`=?',
        [ $p->id, ], false, 'fid');
    }

    /**
     * @param PhpPdo $pdo
     * @param Player $p
     * @param integer $flags
     * @param integer $nflag
     * @return Object
     */
    public static function getsByFlags(PhpPdo $pdo, Player $p, $flags, $nflag = null)
    {
        $sql = 'SELECT * FROM `zhanhd.player`.`PlayerRelation` WHERE `pid`=? AND `flags`&?';
        $binds = [ $p->id, $flags, ];
        if ($nflag) {
            $sql .= ' AND !(`flags`&?)';
            $binds[] = $nflag;
        }
        return self::buildSet($pdo, __CLASS__, $sql, $binds, true, 'fid');
    }

    /**
     * @return integer
     */
    public function lastLikedDay()
    {
        return (integer) date('Ymd', $this->lastLiked / 1000000);
    }

    /**
     * @return Object
     */
    public function getBonus()
    {
        return Relation\Love::gets($this->phppdo, $this);
    }

    /**
     * @param PhpPdo $pdo
     * @param Player $p
     * @return void
     */
    public static function clearWaitingAndConfirm(PhpPdo $pdo, $p)
    {
        $stmt = $pdo->prepare('DELETE FROM `zhanhd.player`.`PlayerRelation` WHERE (`pid`=? or `fid`=?) AND `flags`&?');
        $stmt->execute([
            $p->id,
            $p->id,
            self::FLAG_WAITING|self::FLAG_CONFIRM,
        ]);
    }
}

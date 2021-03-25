<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Config;

/**
 *
 */
use System\Stdlib\Object,
    System\Object\ConfigObject;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Object\Player\Entity as PlayerEntity,
    Zhanhd\Object\Player\Entity\Property as PlayerEntityProperty;

/**
 *
 */
class Entity extends ConfigObject
{
    /**
     * @struct resource
     *
     * @id        integer
     * @tag       string
     * @type      integer
     * @cost      integer
     * @rarity    integer
     * @lvlreq    integer
     * @salable   integer
     * @diffrace  integer
     * @stackable integer
     */

    /**
     * @struct hero inherit resource
     *
     * @army     array( (integer)aid => (integer)level )
     * @skills   array( (integer)sid => (integer)level )
     * @property EntityProperty
     */

    /**
     * @struct EntityProperty
     *
     * @dexext  integer
     * @dexmax  integer
     * @dexmin  integer
     * @dynasty integer
     * @intext  integer
     * @intmax  integer
     * @intmin  integer
     * @stmext  integer
     * @stmmax  integer
     * @stmmin  integer
     * @strext  integer
     * @strmax  integer
     * @strmin  integer
     */

    /**
     * @struct equip inherit resource
     *
     * @rules   array( (integer)atype => true )
     * @effects array( (integer)eid   => (integer)value )
     */

    /**
     * @const integer
     */
    const TYPE_RESOURCE = 10;
    const TYPE_MONEY    = 11;

    const TYPE_CHEST    = 12;
    const TYPE_PROP     = 13;
    const TYPE_RANDPACK = 14;
    const TYPE_GIFT     = 16;
    const TYPE_GROWPACK = 17;
    const TYPE_FRAGMENT = 18;

    const TYPE_SOUL     = 15;

    const TYPE_HERO     = 20;
    const TYPE_WEAPON   = 21;
    const TYPE_ARMOR    = 22;
    const TYPE_HORSE    = 23;
    const TYPE_JEWEL    = 24;
    const TYPE_RING     = 25;

    /**
     * @var $propSet
     */
    private static $propSet = [
        self::TYPE_CHEST    => true,
        self::TYPE_PROP     => true,
        self::TYPE_RANDPACK => true,
        self::TYPE_GIFT     => true,
        self::TYPE_GROWPACK => true,
        self::TYPE_FRAGMENT => true,
    ];

    /**
     * @var $equipSet
     */
    private static $equipSet = [
        self::TYPE_WEAPON => true,
        self::TYPE_ARMOR  => true,
        self::TYPE_HORSE  => true,
        self::TYPE_JEWEL  => true,
        self::TYPE_RING   => true,
    ];

    /**
     * @return boolean
     */
    public function isMoney()
    {
        return $this->type == self::TYPE_MONEY;
    }

    /**
     * @return boolean
     */
    public function isResource()
    {
        return $this->type == self::TYPE_RESOURCE;
    }

    /**
     * @return boolean
     */
    public function isProp()
    {
        return isset(self::$propSet[$this->type]);
    }

    /**
     * @return boolean
     */
    public function isEquip()
    {
        return isset(self::$equipSet[$this->type]);
    }

    /**
     *
     * @return boolean
     */
    public function isForgeable()
    {
        return $this->isEquip();
    }

    /**
     * @param PlayerEntity $pe
     * @return boolean
     */
    public function isSuitable($pe)
    {
        if (false === $this->isEquip()) {
            return false;
        }
        if ($pe->lvl < $this->lvlreq) {
            return false;
        }
        if (false === isset($this->rules[$pe->a->type])) {
            return false;
        }
        return true;
    }

    /**
     * @return integer
     */
    public function getDynasty()
    {
        return (integer) ($this->id / 100);
    }

    /**
     * @return integer
     */
    public function getTalentScore()
    {
        switch ($this->rarity) {
        case 0:
            return 0;
        case 1:
            return 1;
        case 2:
            return 5;
        case 3:
            return 10;
        case 4:
            return 20;
        case 5:
            return 50;
        }
    }

    /**
     * @param  boolean $isCaptain
     * @return integer
     */
    public function getMarryProb($isCaptain)
    {
        switch ($this->rarity) {
            case 1:
            case 2:
            case 3:
                $prob = 99;
                break;
            case 4:
                $prob = 70;
                break;
            case 5:
                $prob = 50;
                break;
        }

        return $isCaptain ? $prob + 10 : $prob;
    }

    /**
     * @param integer $times
     * @return integer
     */
    public function pick($times)
    {
        if ($this->type != self::TYPE_RANDPACK) {
            return [];
        }
        $seed = 0;
        foreach ($this->drops as $o) {
            $seed += $o->prob;
        }

        $picked = [];
        for ($i = 0; $i < $times; $i++) {
            $rand = mt_rand(1, $seed);
            foreach ($this->drops as $o) {
                if ($rand <= $o->prob) {
                    if (Store::has('egroup', $o->k)) {
                        for ($j = 0; $j < $o->v; $j++) {
                            $eid = Store::get('egroup', $o->k)->pickone();
                            if (isset($picked[$eid])) {
                                $picked[$eid] ++;
                            } else {
                                $picked[$eid] = 1;
                            }
                        }
                    } else if (Store::has('entity', $o->k)) {
                        if (isset($picked[$o->k])) {
                            $picked[$o->k] += $o->v;
                        } else {
                            $picked[$o->k] = $o->v;
                        }
                    }

                    break;
                }
                $rand -= $o->prob;
            }
        }
        return $picked;
    }

    /**
     * @param integer $lvl
     * @param integer $ehc
     * @return PlayerEntity
     */
    public function toPe($lvl, $ehc)
    {
        if ($this->type != self::TYPE_HERO) {
            return false;
        }

        $army = $this->army;
        $a = Store::get('army', key($army));
        $o = new PlayerEntity;
        $o->e = $this;
        $o->a = $a;

        $o->property = new PlayerEntityProperty;
        foreach (['dex', 'int', 'stm', 'str'] as $p) {
            if (false === isset($this->property->$p)) {
                $o->property->$p = isset($this->property->{$p.'min'}) ? $this->property->{$p.'min'} : 0;
            } else {
                $o->property->$p = $this->property->$p;
            }
        }

        $o->enhance = Store::get('enhance', $ehc);
        $o->eid = $this->id;
        $o->lvl = $lvl;
        return $o;
    }

    /**
     * @var array
     */
    public static $propKeys = [
        'str' => ['strmin', 'strmax', 'strext', 'strori'],
        'int' => ['intmin', 'intmax', 'intext', 'intori'],
        'stm' => ['stmmin', 'stmmax', 'stmext', 'stmori'],
        'dex' => ['dexmin', 'dexmax', 'dexext', 'dexori'],
    ];

    /**
     * @return array
     */
    public function random()
    {
        if ($this->type != self::TYPE_HERO) {
            return false;
        }

        $ret = new Object;
        foreach (self::$propKeys as $key => list($min, $max, $ext, $ori)) {
            $val = mt_rand($this->property->$min, $this->property->$max);

            if (isset($this->property->$ext)) {
                $buf = mt_rand(0, $this->property->$ext);
            } else {
                $buf = 0;
            }

            $ret->$ori = $val;
            $ret->$ext = $buf;
            $ret->$key = $val + $buf;
       }

        return $ret;
    }

    /**
     * @param integer $lvl
     * @return integer
     */
    public function maxSkillPoint($lvl)
    {
        $points = 0;
        foreach ($this->skills as $reqlvl) {
            $points += $lvl < $reqlvl ? 0 : 4;
        }
        return $points;
    }

    /**
     * @return array
     */
    public function getRevSkills()
    {
        return array_reverse(array_keys($this->skills));
    }
}

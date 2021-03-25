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
use ErrorException;

/**
 *
 */
use System\Stdlib\Object,
    System\Stdlib\PhpPdo,
    System\Object\DatabaseObject;

/**
 *
 */
use Zhanhd\Config\Store   as Store,
    Zhanhd\Config\Skill   as SourceSkill,
    Zhanhd\Config\Entity  as SourceEntity,
    Zhanhd\Config\Enhance,
    Zhanhd\Config\HeroExp;

/**
 *
 */
use Zhanhd\Object\Player as Owner,
    Zhanhd\Extension\Identity;

/**
 *
 */
class Entity extends DatabaseObject
{
    /**
     * @var integer
     */
    const FLAG_UNUSE = 1;
    const FLAG_INUSE = 2;

    /**
     * @var Source...
     */
    public $e = null,
           $a = null;

    /**
     * @var Object
     */
    public $enhance = null;

    /**
     * @var Property
     */
    public $property = null;

    /**
     * @var skill
     */
    public $skill = null;

    /**
     * @param integer $energy
     * @return void
     */
    public function consumeEnergy($energy)
    {
        $this->property->energy = $this->property->getEnergy() - $energy;
        $this->property->energyusedat = $this->ustime;
        $this->property->save();
    }

    /**
     * @param integer $energy
     * @return void
     */
    public function addEnergy($energy)
    {
        $this->property->energy = $this->property->getEnergy() + $energy;
        $this->property->energyusedat = $this->ustime;
        $this->property->save();
    }

    /**
     *
     * @return Object
     */
    public function getEnabledSkills()
    {
        /* todo: setup player-entity-skill-level */
        $enabled = [];
        foreach ($this->e->skills as $sid => $lvl) {
            if ($this->lvl < $lvl) {
                continue;
            }

            if (($s = Store::get('skill', $sid)) === null) {
                throw new \Exception(sprintf('notfound skill %d', $sid));
            }
            $s = new SourceSkill($s);
            $s->setLevel($this->skill->$sid?:1);
            $enabled[$sid] = (new Object)->import([
                's' => $s,
                'l' => $lvl,
            ]);
        }

        return $enabled;
    }

    /**
     * @return integer
     */
    public function getexp()
    {
        switch ($this->e->rarity) {
        case 1:
            return 100;
        case 2:
            return 200;
        case 3:
            return 500;
        case 4:
            return 1000;
        case 5:
            return 1500;
        }
    }

    /**
     *
     * @param  integer $exp
     * @return boolean
     */
    public function addexp($exp)
    {
        if ($this->lvl == HeroExp::MAX_LEVEL) {
            return false;
        }

        $exps = Store::get('heroexp');
        $upgraded   = false;
        $this->exp += $exp;
        $this->lvl  = (integer) $this->lvl;
        
        while ($this->lvl < HeroExp::MAX_LEVEL) {
            if ($this->exp < $exps[$this->lvl]->exp) {
                break;
            }

            $this->exp -= $exps[$this->lvl]->exp;
            $this->lvl ++;
            $upgraded = true;
        }

        if ($this->lvl == HeroExp::MAX_LEVEL) {
            $this->exp = 0;
        }

        return $upgraded;
    }

    /**
     *
     * @param  integer $peid
     * @param  integer $pid
     * @return boolean
     */
    public function findByPid($peid, $pid)
    {
        return $this->findBySql(sprintf('SELECT %s FROM %s WHERE `id` = ? AND `pid` = ? LIMIT 1',
            $this->getSelectColumns(),
            $this->schema()
        ), array(
            $peid,
            $pid,
        ));
    }

    /**
     * @param SourceEntity $e
     * @param integer      $lvl
     * @param integer      $ehc
     * @return PlayerEntity
     */
    public static function fromSourceEntity(SourceEntity $e, $lvl, $ehc)
    {
        $army = $e->army;
        $a = Store::get('army', key($army));

        $o = new static;
        $o->e = $e;
        $o->a = $a;

        $o->property = new Object;
        $o->skill    = new Object;
        foreach ($e->property as $k => $v) {
            $o->property->$k = $v;
        }
        $o->property->aid     = $a->id;

        foreach (['dex', 'int', 'stm', 'str'] as $p) {
            if (false === isset($o->property->$p)) {
                $o->property->$p = isset($o->property->{$p.'min'}) ? $o->property->{$p.'min'} : 0;
            }
        }

        foreach ($e->skill as $sid => $lvl) {
            $o->skill->$sid = $ehc>=5?5:$ehc+1;
        }
        return $o;
    }

    /**
     *
     * @param  PhpPdo $pdo
     * @param  Owner  $o
     * @return Object
     */
    public static function gets(PhpPdo $pdo, Owner $o)
    {
        return self::buildSet($pdo, __CLASS__, 'SELECT * FROM `zhanhd.player`.`PlayerEntity` WHERE `pid` = ?', array(
            $o->id
        ), true, 'id');
    }

    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerEntity`';
    }

    /**
     *
     * @return array
     */
    public function columns()
    {
        return [
            'id'    => null,
            'pid'   => 0,
            'eid'   => 0,
            'lvl'   => 0,
            'exp'   => 0,
            'cnt'   => 0,
            'flags' => 0,
            'gid'   => 0,
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
        $this->property = new Entity\Property;
        $this->skill    = new Entity\Skill;
    }

    /**
     * @return Entity\Refine
     */
    public function getRefine()
    {
        $refine = new Entity\Refine;
        $refine->setPlayerEntityId($this->id);
        $refine->find();
        return $refine;
    }

    /**
     * @param traversable $source
     * @param traversable $target
     * @return void
     */
    public function copyProp($source, $target)
    {
        foreach (SourceEntity::$propKeys as $key => list($ignore1, $ignore2, $ext, $ori)) {
            $target->$ext = isset($source->$ext) ? $source->$ext : 0;
            $target->$ori = $source->$ori;
            $target->$key = $source->$key;
        }
    }

    /**
     * @return integer
     */
    public function maxSkillPoint()
    {
        return $this->e->maxSkillPoint($this->lvl);
    }

    /**
     * @return array
     */
    public function getUpgradableSkills()
    {
        $ret = [];
        foreach ($this->e->skills as $sid => $lvl) {
            if ($this->lvl < $lvl || $this->skill->$sid >= 5) {
                continue;
            }
            $ret[$sid] = $this->skill->$sid ? 5-$this->skill->$sid : 4;
        }
        return $ret;
    }

    /**
     * @return array
     */
    public function randomGuarantee()
    {
        if ($this->e->type != SourceEntity::TYPE_HERO) {
            return false;
        }

        $ret = new Object;
        foreach (SourceEntity::$propKeys as $key => list($min, $max, $ext, $ori)) {
            if ($this->property->$ori == $this->e->property->$max) {
                $val = $this->e->property->$ori;
            } else {
                $val = mt_rand($this->property->$ori, $this->e->property->$max);
            }

            if (isset($this->e->property->$ext)) {
                $buf = mt_rand($this->property->$ext, $this->e->property->$ext);
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
     *
     * @return void
     */
    protected function postSelect()
    {
        $this->e = Store::get('entity', $this->eid);
        
        $this->property->setPlayerEntityId($this->id);
        $this->property->find();
        $this->skill->setPlayerEntityId($this->id);
        $this->skill->find();
        switch ($this->e->type) {
        case SourceEntity::TYPE_HERO:
            $this->a       = Store::get('army', $this->property->aid);
            break;
        }
    }

    /**
     *
     * @return void
     */
    protected function preInsert()
    {
        $this->id = Identity::generatePeId($this->retrieveScope('globals')->redis);
        $this->lvl   = 1;
        $this->exp   = 0;
        $this->cnt   = 1;
        $this->flags = self::FLAG_UNUSE;

        switch ($this->e->type) {
        case SourceEntity::TYPE_HERO:
            $this->copyProp($this->e->random(), $this->property);
            $this->property->energy = 100;
            $army = $this->e->army;
            $this->property->aid    = key($army);

            break;
        }
    }

    /**
     *
     * @return void
     */
    protected function postInsert()
    {
        $this->property->setPlayerEntityId($this->id);
        $this->property->save();
        $this->skill->setPlayerEntityId($this->id);
        $this->skill->save();
    }

    /**
     *
     * @return void
     */
    protected function postUpdate()
    {
        if ($this->flags == self::FLAG_INUSE && $this->gid == 1) {
            $g = $this->retrieveScope('globals');
            $g->uplineup = true;
        }
        $this->property->save();
        $this->skill->save();
    }

    /**
     *
     * @return void
     */
    protected function preDelete()
    {
        $this->property->drop();
        $this->skill->drop();
    }
}

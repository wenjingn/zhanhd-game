<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Object\Player\Lineup;

/**
 *
 */
use System\Stdlib\PhpPdo,
    System\Stdlib\Object,
    System\Object\DatabaseObject;

/**
 *
 */
use Zhanhd\Object\Player\Entity as PlayerEntity,
    Zhanhd\Object\Player\Lineup as PlayerLineup;

/**
 *
 */
class Hero extends DatabaseObject
{
    /**
     * @var Equip
     */
    public $equips = null;

    /**
     * @var PlayerEntity;
     */
    public $pe = null;

    /**
     *
     * @return Object
     */
    public function getEquipEntities()
    {
        return $this->equips->getIterator()->filter(function($peid) {
            return (boolean) $peid;
        })->map(function($peid) {
            $pe = new PlayerEntity;
            $pe->findByPid($peid, $this->pid);

            return $pe;
        });
    }

    /**
     * @param PlayerLineup $pl
     * @return integer
     */
    public function getPower(PlayerLineup $pl)
    {
        $this->pe->enhance = new Object;
        $equips = $this->getEquipEntities();
        return (int)($this->getHp($pl, $equips)/6 + $this->getDamage($pl, $equips) + $this->getDefence($pl, $equips));
    }

    /**
     * @param PlayerLineup $pl
     * @param Object $equips
     * @return integer
     */
    public function getDamage(PlayerLineup $pl, $equips)
    {
        $army = $this->pe->a;
        $base = ($army->dmg + $army->dmgperlvl * $this->pe->lvl) * (1 + $this->pe->enhance->dmg / 100);
        
        $dmgrate = 0;
        $strrate = (int)($this->pe->property->str * (1 + $this->pe->enhance->str / 100));
        if ($pl->f && isset($pl->f->additions[$army->type])) {
            if (isset($pl->f->additions[$army->type][10001])) {
                $dmgrate += $pl->f->additions[$army->type][10001];
            }
        }

        foreach ($equips as $o) {
            if (false === isset($o->e->rules[$army->type])) {
                continue;
            }

            if (isset($o->e->effects[10001])) {
                $dmgrate += $o->e->effects[10001];
            }

            if (isset($o->e->effects[10006])) {
                $strrate += $o->e->effects[10006];
            }
        }

        return (int)($base * (1 + $dmgrate/100) * (1 + $strrate/100));
    }

    /**
     * @param PlayerLineup $pl
     * @param Object $equips
     * @return integer
     */
    public function getHp(PlayerLineup $pl, $equips)
    {
        $army = $this->pe->a;
        $base = $army->hpt + $army->hptperlvl * $this->pe->lvl;

        $hptrate = 0;
        if ($pl->f && isset($pl->f->additions[$army->type])) {
            if (isset($pl->f->additions[$army->type][10004])) {
                $hptrate += $pl->f->additions[$army->type][10004];
            }
        }

        foreach ($equips as $o) {
            if (false === isset($o->e->rules[$army->type])) {
                continue;
            }

            if (isset($o->e->effects[10004])) {
                $hptrate += $o->e->effects[10004];
            }
        }

        return (int)($base * (1 + $this->pe->enhance->hpt/100 + $hptrate/100));
    }

    /**
     * @param PlayerLineup $pl
     * @param Object $equips
     * @return integer
     */
    public function getDefence(PlayerLineup $pl, $equips)
    {
        $army = $this->pe->a;
        $base = ($army->def + $army->defperlvl * $this->pe->lvl) * (1 + $this->pe->enhance->def / 100);
        $defrate = 0;
        if ($pl->f && isset($pl->f->additions[$army->type])) {
            if (isset($pl->f->additions[$army->type][10002])) {
                $defrate += $pl->f->additions[$army->type][10002];
            }
        }

        foreach ($equips as $o) {
            if (false === isset($o->e->rules[$army->type])) {
                continue;
            }
            
            if (isset($o->e->effects[10002])) {
                $defrate += $o->e->effects[10002];
            }
        }
        
        $prop = 0;
        $prop += (int)($this->pe->property->str * (1 + $this->pe->enhance->str/100));
        $prop += (int)($this->pe->property->stm * (1 + $this->pe->enhance->stm/100));
        $prop += (int)($this->pe->property->dex * (1 + $this->pe->enhance->dex/100));

        return (int)($base * (1 + $defrate/100 + $prop/300));
    }

    /**
     *
     * @param  PhpPdo       $pdo
     * @param  PlayerLineup $pl
     * @return Object
     */
    public static function gets(PhpPdo $pdo, PlayerLineup $pl)
    {
        return self::buildSet($pdo, __CLASS__, 'SELECT * FROM `zhanhd.player`.`PlayerLineupHero` WHERE `pid` = ? AND `gid` = ?', array(
            $pl->pid,
            $pl->gid,
        ), true);
    }

    /**
     *
     * @return string
     */
    public function schema()
    {
        return '`zhanhd.player`.`PlayerLineupHero`';
    }

    /**
     *
     * @return array
     */
    public function columns()
    {
        return [
            'pid'  => 0,
            'gid'  => 0,
            'pos'  => 0,
            'peid' => 0,
        ];
    }

    /**
     *
     * @return array
     */
    public function primary()
    {
        return [
            'pid' => null,
            'gid' => null,
            'pos' => null,
        ];
    }

    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->equips = new Equip;
        $this->pe     = new PlayerEntity;
    }

    /**
     *
     * @return void
     */
    protected function postSelect()
    {
        $this->equips->setPlayerId($this->pid);
        $this->equips->setGroupId ($this->gid);
        $this->equips->setPosition($this->pos);
        $this->equips->find();

        if ($this->peid) {
            $this->pe->find($this->peid);
        }
    }

    /**
     *
     * @return void
     */
    protected function postInsert()
    {
        $this->equips->setPlayerId($this->pid);
        $this->equips->setGroupId ($this->gid);
        $this->equips->setPosition($this->pos);
        $this->equips->save();
    }

    /**
     *
     * @return void
     */
    protected function postUpdate()
    {
        $this->equips->save();
    }

    /**
     *
     * @return void
     */
    protected function preDelete()
    {
        $this->equips->drop();
    }
}

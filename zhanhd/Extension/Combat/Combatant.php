<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Extension\Combat;

/**
 *
 */
use System\Stdlib\Object;

/**
 *
 */
use Zhanhd\Config\Store,
    Zhanhd\Config\Army               as SourceArmy,
    Zhanhd\Config\Forge              as SourceForge,
    Zhanhd\Config\Skill              as SourceSkill,
    Zhanhd\Config\Skill\Effect       as SourceSkillEffect,
    Zhanhd\Config\Formation          as SourceFormation,
	Zhanhd\Object\Player\Lineup      as PlayerLineup,
    Zhanhd\Object\Player\Lineup\Hero as PlayerLineupHero;

/**
 *
 */
use Exception;

/**
 *
 * aura will not be removed when supplier died
 * only check army&dynasty on aura
 */
class Combatant
{
    /**
     * @var const
     */
    const CAMP_ATTACKER = 1;
    const CAMP_DEFENDER = 2;

    /**
     * @var PlayerLineupHero
     */
    public $plh = null;

    /**
     * @var integer
     */
    private $camp = null;

    /**
     * @var integer
     */
    private $priority  = null,
            $atkCount  = null,
            $curHpoint = null,
            $rawHpoint = null;

    /**
     * @var mixed
     */
    private $enemies    = null,
            $restraint  = null,
            $additions  = null,
            $equipments = null,
            $forge      = null;

    /**
     * @var Object
     */
    private $atts = null,
            $hits = null,
            $anti = null,
            $effs = null;

	/**
	 * @var Object
	 */
	private $bonds = null;

    /**
     * @var Object
     */
    private $auraSupplied = null,
            $auraAccepted = null;

    /**
     * @var boolean
     */
    private $confused = null,
            $bleeding = null,
            $specKill = null;

    /**
     * @var double
     */
    private $passiveEffectAddition = 1;

    /**
     * @const integer
     */
    const BUFF_CAVALRY  = 1;
    const BUFF_SPEARMAN = 2;
    const BUFF_ARCHER   = 3;
    const BUFF_INFANTRY = 4;

	/**
	 * @const integer
	 */
	const BOND_CRIT    = 1;
	const BOND_DAMAGE  = 2;
	const BOND_DEFENCE = 3;
	const BOND_TRIGGER = 4;
	const BOND_CRITRAT = 5;
	const BOND_HPOINT  = 6;
	const BOND_SPEED   = 7;

    /**
     * @var integer
     */
    public $pointsRaceBuff = 0;

    /**
     * @var
     */
    private $compileEffects = null;

    /**
     *
     * @param  PlayerLineupHero $plh
     * @param  integer          $camp
     * @param  SourceFormation  $formation
     * @param  boolean          $restraint
     * @return void
     */
    public function __construct(PlayerLineupHero $plh, $camp, SourceFormation $formation = null, $restraint = false, $bonds = null)
    {
        $plh->pe->enhance = new Object;
        $this->plh  = $plh;
        $this->camp = $camp;

        $this->atkCount   = 0;
        $this->restraint  = $restraint;
        $this->additions  = new Object;
        $this->equipments = new Object;
        $this->forge      = new Object;
		$this->bonds      = new Object;

        $this->confused = false;
        $this->bleeding = false;
        $this->specKill = false;

		/**
		 * @bonds
		 */
		$this->bonds = new Object;
		if (false === empty($bonds)) {
			foreach ($bonds as $bid) {
				$bond = Store::get('bond', $bid);
				foreach ($bond->effects as $type => $value) {
					switch ($type) {
					case self::BOND_DAMAGE:
					case self::BOND_DEFENCE:
					case self::BOND_HPOINT:
						if (null === $this->bonds->$type) {
							$this->bonds->$type = [];
						}
						$this->bonds->$type->set(null, $value);
						break;
					default:
						$this->bonds->$type += $value;
						break;
					}
				}
			}
		}

        /**
         * @marry effects
         * @ring-id 250101
         */
        if ($this->plh->pe->property->married) {
            $ring = Store::get('entity', 250101);

            if (isset($ring->rules[$this->plh->pe->a->type])) {
                foreach ($ring->effects as $k => $v) {
                    $this->equipments->$k += $v;
                }
            }
        }

        // equipments effects
        foreach ($plh->getEquipEntities()->filter(function($pe) {
            if ($pe->e->lvlreq > $this->plh->pe->lvl) {
                return false;
            }

            return isset($pe->e->rules[$this->plh->pe->a->type]);
        }) as $pe) {
            foreach ($pe->e->effects as $k => $v) {
                $this->equipments->$k += $v;
            }

            if ($pe->property->forge && Store::has('forge', $pe->property->forge)) {
                switch ($pe->e->property->forgeType) {
                case 1: $this->forge->dmg += $pe->property->forge * $pe->e->property->forgeAdd; break;
                case 2: $this->forge->def += $pe->property->forge * $pe->e->property->forgeAdd; break;
                case 3: $this->forge->vit += $pe->property->forge * $pe->e->property->forgeAdd; break;
                }
            }
        }

        // formation additions effects
        if ($formation && isset($formation->additions[$this->plh->pe->a->type])) {
            foreach ($formation->additions[$this->plh->pe->a->type] as $eid => $num) {
                $this->additions->{$eid} += $num;
            }
        }
        // initialize skill effects
        $this->initializeSkills();

        $this->compileEffects = array();
    }

    /**
     *
     * @return void
     */
    public function debug()
    {
        printf("peid=%2d, eid=%d, rawspd=%d, curspd=%d, pos=%d, hp=%6d, dmg=%d, def=%d, confused=%d, bleeding=%d, passiveEffectAddition=%f\n",
            $this->plh->peid, $this->plh->pe->eid,
            $this->priority, $this->getCurSpeed(),
            $this->plh->pos,
            $this->getHpoint(),
            $this->getRawDamage(), $this->getRawDefence(),
            $this->confused, $this->bleeding, $this->passiveEffectAddition
        );
        var_dump('triggerSkill:');
        print_r($skill = $this->triggerActiveSkill());
        var_dump('triggerHitsEffect');
        print_r($this->triggerHitsEffects($skill));
        var_dump('triggerCriticalDamage');
        var_dump($this->triggerCriticalDamage());
    }

    /**
     *
     * @return Object
     */
    public function attack()
    {
        $retval = (new Object)->import(array(
            'update'   => [],
            'killed'   => [],
            'sequence' => [
                'skill'     => 0,
                'combatant' => $this,
                'effects'   => [], // runtime effects; effect.gid => flag
                'involved'  => [],
                'queue'     => [],
            ],
        ));

        if ($this->confused) {
            $this->confused = false;

            $retval->update->set($this->plh->peid, true);
            return $retval;
        }

        // trigger active-skill to decide attack-mode
        if (($skill = $this->triggerActiveSkill())) {
            $mode = $skill->s->mode;
            $rand = $skill->s->nums;

            // remove this active-skill if it can be triggered only once
            if ($skill->s->once) {
                unset($this->atts->{$skill->s->id});
            }

            $retval->sequence->skill = $skill->s->id;
        } else {
            $mode = 'single';
            $rand = 0;
        }

        // find out targets
        $targets = [];
        foreach (CombatTarget::$mode($this->plh->pos) as $pos => $groups) {
            if ($rand) {
                foreach ($groups as $pos) {
                    if (isset($this->enemies->$pos) && $this->enemies->$pos->getHpoint()) {
                        $targets[] = $pos;
                        if (count($targets) == $rand) {
                            break;
                        }
                    }
                }
            } else if (isset($this->enemies->$pos) && $this->enemies->$pos->getHpoint()) {
                $targets = $groups;
                break;
            }
        }

        // wtf
        if (count($targets) == 0) {
            throw new Exception("invalid combat targets for $mode($rand)");
        }

        // target defending
        $thornsum = 0;
        $dmgRatio = 0;

        if (($incr = $this->getPropValue(array(
            10021 => 0,
        )))) {
            $x = (integer) ($this->curHpoint * 100 / $this->rawHpoint);
            $o = $this->effs->get(10021)->head();
            if (($o->o->army - $o->o->dynasty) >= $x) {
                $dmgRatio += (integer) (pow(1 + $incr/100, ((integer) (($o->o->army - $x) / $o->o->dynasty))) * 100);
                $retval->sequence->effects->set(13, 1);
            }
        }

        if ($this->atkCount) {
            $incRatio = $this->getPropValue(array(
                10023 => 0,
            )) * pow(2, $this->atkCount);
            if ($incRatio) {
                $retval->sequence->effects->set(1, 1);
                $dmgRatio += $incRatio;
            }
        }

        foreach ($targets as $pos) {
            if (null === ($target = $this->enemies->$pos) || $target->getHpoint() == 0) {
                // maybe in full-attack-mode, the target not exists or already died
                continue;
            }

            // skill and hits effects
            $effects = $this->triggerHitsEffects($skill);

            // defending
            list($hp, $dmg, $thorndmg, $flags, $runtimeEffects) = $target->defend($this, $effects, ($skill ? $skill->s->getValue() : 100) + $dmgRatio, 0, $skill);

            // damage thorned
            if ($thorndmg) {
                $thornsum += $thornsum;
            }

            // died or bleeding
            if ($hp == 0) {
                $retval->killed->set($target->plh->peid, true);
            } else if ($flags & self::FLAG_BLEED) {
                $retval->update->set($target->plh->peid, true);
            }

            // effects
            if ($flags & self::FLAG_DIFFRACE) {
                $retval->sequence->effects->set(11, 1);
            }

            // involved
            $retval->sequence->involved->set(null, array(
                'combatant' => $target,
                'damage'    => $dmg,
                'hpoint'    => $hp,
                'flags'     => $flags,
                'effects'   => $runtimeEffects,
            ));
        }

        // checking thorn-damage
        if (isset($this->anti->{10012}) && $thornsum) {
            if ($this->curHpoint <= $thornsum) {
                $this->curHpoint  = 0;
            } else {
                $this->curHpoint -= $thornsum;
            }

            // thorn effects

            // involved
            $retval->sequence->involved->set(null, array(
                'combatant' => $this,
                'damage'    => $thornsum,
                'hpoint'    => $this->curHpoint,
                'flags'     => 0,
                'effects'   => [],
            ));
        }

        // update
        if ($this->curHpoint) {
            $retval->update->set($this->plh->peid, true);
        } else {
            $retval->killed->set($this->plh->peid, true);
        }

        $this->atkCount++;
        return $retval;
    }

    /**
     * @var integer
     */
    const FLAG_NONE     = 0;
    const FLAG_CRIT     = 1;
    const FLAG_KILL     = 2;
    const FLAG_BLEED    = 4;
    const FLAG_CONFUSED = 8;
    const FLAG_DIFFRACE = 16;

    /**
     *
     * @param  Combatant $target
     * @param  array     $effects
     * @param  integer   $dmgratio
     * @param  integer   $defratio
     * @param  Object    $skill
     * @return array
     */
    public function defend(Combatant $target, array $effects, $dmgratio, $defratio = 0, Object $skill = null)
    {
        $flags  = self::FLAG_NONE;
        $damage = 0;
        $thorns = 0;
        $runtimeEffects = array(); // runtime effects; effect.gid => flag

        if ($skill) {
            if ($this->getPropValue(array(
                10025 => 0,
            ))) {
                goto dmgReady;
            }
        } else {
            if ($this->getPropValue(array(
                10024 => 0,
            ))) {
                goto dmgReady;
            }
        }

        // special&hits effects
        foreach ($effects as $eid => $value) {
            switch ($eid) {
            case 10011:
                if ($this->anti->get($eid)) {
                    break;
                }

                if ($this->curHpoint * 100 < $this->rawHpoint * $value) {
                    $damage = $this->curHpoint;
                    $this->curHpoint = 0;
                    $flags |= self::FLAG_KILL;
                    $this->specKill = true;
                    goto retval;
                }

                break;

            case 10010: // bleeding
                if ($this->anti->get($eid) || $this->bleeding) {
                    break;
                }

                $this->bleeding = true;
                $flags |= self::FLAG_BLEED;
                $runtimeEffects[10] = 0;
                break;

            case 10009: // confused
                if ($this->anti->get($eid)) {
                    break;
                }

                $this->confused = true;
                $flags |= self::FLAG_CONFUSED;
                $runtimeEffects[7] = 0;
                break;

            case 10001: // diffrace
                if ($dmgratio && $this->plh->pe->e->diffrace) {
                    $dmgratio += $value;
                    $flags |= self::FLAG_DIFFRACE;
                }

                break;

            case 10002: // reduce-defence
                $defratio -= $value;
                break;

            case 10026: // 固定伤害
                if ($this->anti->get($eid)) {
                    break;
                }

                $damage = (integer) (($this->curHpoint * $value + 99) / 100);
                break;
            }
        }

        // 固定伤害 技能
        if ($damage) {
            goto dmgReady;
        }

        // some skills just have some special effects
        if ($dmgratio == 0) {
            goto retval;
        }

        // real-damage * 10000 * 10000
        $dmgex = $target->getRawDamage() * $dmgratio * mt_rand($target->getPropValue(array(
            10015 => 80,
        )), $this->getPropValue(array(
            10014 => 120,
        )));

        if (($incr = $this->getPropValue(array(
            10022 => 0,
        )))) {
            $x = (integer) ($this->curHpoint * 100 / $this->rawHpoint);
            $o = $this->effs->get(10022)->head();
            if (($o->o->army - $o->o->dynasty) >= $x) {
                $defratio += (integer) (pow(1 + $incr/100, ((integer) (($o->o->army - $x) / $o->o->dynasty))) * 100);
                $runtimeEffects[14] = 1;
            }
        }

        // scale up to same level of real-damage
        $defex = $this->getRawDefence($defratio) * 10000;

        // scale up to real-damage * 10000 * 10000 * 10 * 100
        $dmgex = max(($dmgex - $defex) * 10, $dmgex * 2) * $this->getPropValue(array(
            10005 => -100,
        )) * -1;

        if (($cdrto = $target->triggerCriticalDamage())) {
            // critical damage
            $dmgex *= $cdrto;
            $flags |= self::FLAG_CRIT;
        } else {
            // scale up to real-damage * 10000 * 10000 * 10 * 100 * 100
            $dmgex *= 100;
        }

        // scale down to real-damage
        $damage = (integer) (($dmgex + 9999999999999) / 10000000000000);

        // thorns
        if ($thornrto = $this->getPropValue(array(
            10012 => 0,
        ))) {
            $thorns = (integer) (($damage * $thornrto + 99) / 100);
        }

        dmgReady:

        // reduce hpoint
        if ($this->curHpoint <= $damage) {
            $this->curHpoint  = 0;
        } else {
            $this->curHpoint -= $damage;
        }

        retval: return array(
            $this->curHpoint,
            $damage,
            $thorns,
            $flags,
            $runtimeEffects,
        );
    }

    /**
     *
     * @return void
     */
    public function finalize()
    {
        // sort hits skills
        foreach ($this->hits as $eid => $o) {
            $o->sort(function($a, $b) {
                return $b->o->getValue() - $a->o->getValue();
            });
        }

        // sort aura skills
        foreach ($this->auraAccepted as $eid => $objects) {
            foreach ($objects as $op => $o) {
                $o->sort(function($a, $b) {
                    return $b->o->getValue() - $a->o->getValue();
                });
            }
        }

        // priority for CombatQueue
        $this->priority = $this->getCurSpeed();

        $this->passiveEffectAddition = 1;
        $int_hero = $this->plh->pe->property->int * (100 + $this->plh->pe->enhance->int);
        $int_other= array_sum($this->getPropValue([
            10007 => 0,
            10008 => 0,
        ])) * 100;
        $this->passiveEffectAddition = 1 + ($int_hero + $int_other) / 20000;
    }

    /**
     * @param string $prop
     * @return integer
     */
    private function getOriProp($prop)
    {
        if ($this->bleeding) {
            return ($this->plh->pe->property->$prop + 1) >> 1;
        }

        return $this->plh->pe->property->$prop;
    }

    /**
     * @return integer
     */
    public function getPower()
    {
        return (integer) ($this->getHpoint() / 6 + ($this->getRawDamage() + $this->getRawDefence()) / 10000);
    }

    /**
     * @param integer $hp
     * @return void
     */
    public function setHpoint($hp)
    {
        $this->curHpoint = $hp;
    }

    /**
     *
     * @return integer
     */
    public function getHpoint()
    {
        if (null === $this->curHpoint) {
            $this->curHpoint = $this->getRawHpoint();
        }

        return $this->curHpoint;
    }

    /**
     * @param integer $hp
     * @return void
     */
    public function setRawHpoint($hp)
    {
        $this->rawHpoint = $hp;
    }

    /**
     * @return integer
     */
    public function getRawHpoint()
    {
        /**
         * 10004 vit%
         */
        if (null === $this->rawHpoint) {
			$base = $this->plh->pe->a->hpt + $this->plh->pe->lvl*$this->plh->pe->a->hptperlvl;
            $this->rawHpoint = ceil($this->getPropValue(array(
                10004 => 100 + $this->plh->pe->enhance->hpt,
            )) * $base / 100) + $this->forge->vit;
			if ($this->bonds->{self::BOND_HPOINT}) {
				foreach ($this->bonds->{self::BOND_HPOINT} as $val) {
					$this->rawHpoint += $base*(100+$val)/100;
				}
				$this->rawHpoint = (int)($this->rawHpoint);
			}
        }

        return $this->rawHpoint;
    }

    /**
     *
     * @return integer
     */
    public function getCamp()
    {
        return $this->camp;
    }

    /**
     *
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority * 100 + $this->plh->pos * 10 + $this->camp;
    }

    /**
     *
     * @return integer
     */
    public function getRawSpeed()
    {
        return $this->priority;
    }

    /**
     *
     * @return integer
     */
    public function getCurSpeed()
    {
        /**
         * 10020 dex%
         * 10008 att
         * 10016 dex
         */
        $enhance = 100 + $this->plh->pe->enhance->dex;
        $base = $enhance * $this->getOriProp('dex') + array_sum($this->getPropValue([
                    10008 => 0,
                    10016 => 0,
                ])) * 100;
        $rate = $this->getPropValue([ 10020 => $this->plh->pe->a->dex + $this->getSpeedCorrection() + 100 ]);
        /* scale up to 10000 */
        $speed = $base*$rate;
        if ($this->pointsRaceBuff && $this->pointsRaceBuff == self::BUFF_CAVALRY && $this->plh->pe->a->type == SourceArmy::TYPE_CAVALRY) {
            $speed += $base*10;
        }
		if ($this->bonds->{self::BOND_SPEED}) {
			$speed += $base*$this->bonds->{self::BOND_SPEED};
		}
        return (integer)(30000000000 / (100000000 + 45*$speed));
    }

    /**
     * real-damage * 10000
     *
     * @return integer
     */
    public function getRawDamage()
    {
        /**
         * 10006 str
         * 10008 att
         * 10001 dmg%
         */
        $base    = $this->plh->pe->a->dmg + $this->plh->pe->a->dmgperlvl * $this->plh->pe->lvl;
        $dmgrate = $this->getPropValue([ 10001 => 100 ]);
        $enhance = 100 + $this->plh->pe->enhance->dmg;
        $strrate = 10000 + $this->getOriProp('str') * (100 + $this->plh->pe->enhance->str) + array_sum($this->getPropValue([
            10006 => 0,
            10008 => 0,
        ])) * 100;
        $ret = $base * $dmgrate * $enhance * $strrate + 99999999 + $this->forge->dmg * 100000000;
        if ($this->pointsRaceBuff && $this->pointsRaceBuff == self::BUFF_ARCHER && $this->plh->pe->a->type == SourceArmy::TYPE_ARCHER) {
            $ret += $base*50000000;
        }
		if ($this->bonds->{self::BOND_DAMAGE}) {
			foreach ($this->bonds->{self::BOND_DAMAGE} as $val) {
				$ret += $base*(100000000 + $val*1000000);
			}
		}
        return $ret / 10000;
    }

    /**
     * real-defence * 10000
     *
     * @param  integer $incr
     * @return integer
     */
    public function getRawDefence($incr = 0)
    {
        /**
         * 10002 ac%
         */
        $propsum = $this->getOriProp('int') * (100 + $this->plh->pe->enhance->int)
                 + $this->getOriProp('stm') * (100 + $this->plh->pe->enhance->stm)
                 + $this->getOriProp('dex') * (100 + $this->plh->pe->enhance->dex);

        $base    = $this->plh->pe->a->def + $this->plh->pe->a->defperlvl * $this->plh->pe->lvl;
        $defrate = $this->getPropValue([ 10002 => 100 + $incr ]) + (integer)($propsum / 300);
        $enhance = 100 + $this->plh->pe->enhance->def;
        
        $ret = $base * $defrate * $enhance + $this->forge->def * 10000;
        if ($this->pointsRaceBuff && $this->pointsRaceBuff == self::BUFF_INFANTRY && $this->plh->pe->a->type == SourceArmy::TYPE_INFANTRY) {
            $ret += $base*5000;
        }
		if ($this->bonds->{self::BOND_DEFENCE}) {
			foreach ($this->bonds->{self::BOND_DEFENCE} as $val) {
				$ret += $base*(10000+$val*100);
			}
		}
        return $ret;
    }

    /**
     *
     * @param  Object $enemies
     * @return void
     */
    public function setEnemies(Object $enemies)
    {
        $this->enemies = $enemies;
    }

    /**
     *
     * @return Object
     */
    public function getAura()
    {
        return $this->auraSupplied;
    }

    /**
     *
     * @param  Object    $auras
     * @param  Combatant $src
     * @return void
     */
    public function addAura(Object $auras, Combatant $src)
    {
        if ($src->getCamp() == $this->camp) {
            foreach ($auras->get(SourceSkillEffect::TO_ALLY, array()) as $se) {
                // validate army and dynasty
                if (($se->army    && $se->army    <> $this->plh->pe->a->type) ||
                    ($se->dynasty && $se->dynasty <> $this->plh->pe->e->getDynasty())) {
                    continue;
                }

                if ($se->anti) {
                    $this->anti->get($se->eid, array())->set(null, array(
                        'o' => $se,
                        'h' => $src,
                    ));

                    continue;
                }

                // hit effects
                switch ($se->eid) {
                case 10001:
                    if ($se->diffrace) {
                        if ($se->op == SourceSkillEffect::OP_SUB) {
                            // invalid effect
                            break;
                        }

                        $this->hits->get($se->eid, array())->set(null, array(
                            'o' => $se,
                            'h' => $src,
                        ));
                    } else {
                        $this->auraAccepted->get($se->eid, array(
                            SourceSkillEffect::OP_ADD => [],
                            SourceSkillEffect::OP_SUB => [],
                        ))->get($se->op)->set(null, array(
                            'o' => $se,
                            'h' => $src,
                        ));
                    }

                    break;

                case 10009:
                case 10010:
                case 10011:
                    if ($se->op == SourceSkillEffect::OP_SUB) {
                        // invalid effect
                        break;
                    }

                    $this->hits->get($se->eid, array())->set(null, array(
                        'o' => $se,
                        'h' => $src,
                    ));

                    break;

                default:
                    $this->auraAccepted->get($se->eid, array(
                        SourceSkillEffect::OP_ADD => [],
                        SourceSkillEffect::OP_SUB => [],
                    ))->get($se->op)->set(null, array(
                        'o' => $se,
                        'h' => $src,
                    ));
                }
            }

            return;
        }

        if ($src->getCamp() <> $this->camp) {
            foreach ($auras->get(SourceSkillEffect::TO_ENEMY, array()) as $se) {
                // validate army and dynasty
                if (($se->army    && $se->army    <> $this->plh->pe->a->type) ||
                    ($se->dynasty && $se->dynasty <> $this->plh->pe->e->getDynasty())) {
                    continue;
                }

                $this->auraAccepted->get($se->eid, array(
                    SourceSkillEffect::OP_ADD => [],
                    SourceSkillEffect::OP_SUB => [],
                ))->get($se->op)->set(null, array(
                    'o' => $se,
                    'h' => $src,
                ));
            }

            return;
        }
    }

    /**
     *
     * 深坑慎入
     * @return array
     */
    public function getEffects()
    {
        if (count($this->compileEffects) == 0) {
            $effects = [];

            /* 阵形加成 */
            foreach ($this->additions as $eid => $null) {
                $effects[$eid]['+'] = true;
            }

            /* 被动天赋 */
            foreach ($this->effs as $eid => $objects) {
                foreach ($objects as $o) {
                    switch ($o->o->op) {
                    case SourceSkillEffect::OP_ADD: $effects[$eid]['+'] = true; break;
                    case SourceSkillEffect::OP_SUB: $effects[$eid]['-'] = true; break;
                    }
                }
            }

            /* 光环 */
            foreach ($this->auraAccepted as $eid => $objects) {
                foreach ($objects as $op => $o) {
                    if ($o->count() == 0) {
                        continue;
                    }

                    switch ($op) {
                    case SourceSkillEffect::OP_ADD: $effects[$eid]['+'] = true; break;
                    case SourceSkillEffect::OP_SUB: $effects[$eid]['-'] = true; break;
                    }
                }
            }

            foreach ($effects as $eid => $flags) {
                $e = Store::get('effects', $eid);
                if ($e && $e->gid && $e->runtime == 0) {
                    foreach ($flags as $flag => $null) {
                        $this->compileEffects[$e->gid][$flag] = true;
                    }
                }
            }

            /* 免疫特殊效果 */
            foreach ($this->anti as $eid => $objects) {
                switch ($eid) {
                /* 混乱 击伤 */
                case 10009: $this->compileEffects[ 7]['+'] = true; break;
                case 10010: $this->compileEffects[10]['+'] = true; break;
                }
            }
        }

        $effects = array();

        if ($this->confused) {
            $effects[ 7]['-'] = true;
        }

        if ($this->bleeding) {
            $effects[10]['-'] = true;
        }

        foreach ($this->compileEffects as $eid => $flags) {
            if (isset($effects[$eid])) {
                $effects[$eid] = array_merge($effects[$eid], $flags);
            } else {
                $effects[$eid] = $flags;
            }
        }

        return $effects;
    }

    /**
     *
     * @return void
     */
    private function initializeSkills()
    {
        $this->atts = new Object;
        $this->hits = new Object;
        $this->anti = new Object;

        $this->auraSupplied = new Object;
        $this->auraAccepted = new Object;
        $this->effs = new Object;

        foreach ($this->plh->pe->getEnabledSkills() as $o) {
            switch ($o->s->type) {
            case SourceSkill::TYPE_ACTIVE:
                $this->atts->set($o->s->id, $o);
                break;

            case SourceSkill::TYPE_PASSIVE:
            case SourceSkill::TYPE_INNATE:
            case SourceSkill::TYPE_MATCHED:
                foreach ($o->s->effects as $se) {
                    if ($se->at == SourceSkillEffect::AT_ALL) {
                        $this->auraSupplied->get($se->to, array())->set(null, $se);
                    } else if ($se->to == SourceSkillEffect::TO_ENEMY) {
                        $this->hits->get($se->eid, array())->set(null, array(
                            'o' => $se,
                            'h' => $this,
                        ));
                    } else {
                        switch ($se->eid) {
                        case 10001:
                            if ($se->diffrace) {
                                $this->hits->get($se->eid, array())->set(null, array(
                                    'o' => $se,
                                    'h' => $this,
                                ));
                            } else {
                                $this->effs->get($se->eid, array())->set(null, array(
                                    'o' => $se,
                                    'h' => $this,
                                ));
                            }

                            break;

                        case 10009:
                        case 10010:
                        case 10011:
                        case 10012:
                        case 10026:
                            if ($se->op == SourceSkillEffect::OP_SUB) {
                                // invalid effect
                                break;
                            }

                            if ($se->anti) {
                                $this->anti->get($se->eid, array())->set(null, array(
                                    'o' => $se,
                                    'h' => $this,
                                ));
                            } else {
                                $this->hits->get($se->eid, array())->set(null, array(
                                    'o' => $se,
                                    'h' => $this,
                                ));
                            }
                            break;

                        // case 10021:
                        // case 10022:
                        // case 10023:
                        // case 10024:
                        // case 10025:
                            // break;

                        default:
                            $this->effs->get($se->eid, array())->set(null, array(
                                'o' => $se,
                                'h' => $this,
                            ));
                            break;
                        }
                    }
                }

                break;
            }
        }
    }

    /**
     *
     * @param  array $props
     * @return mixed
     */
    private function getPropValue(array $props)
    {
        if (empty($props)) {
            return 0;
        }

        $idx = 0;
        $ret = [];

        foreach ($props as $prop => $value) {
            // equipments
            $value += $this->equipments->$prop;

            // additions
            $value += $this->additions->$prop;

            // effects
            foreach ($this->effs->get($prop, array()) as $o) {
                switch ($o->o->op) {
                case SourceSkillEffect::OP_ADD:
                    $value += (integer)($o->o->getValue() * $o->h->getPassiveEffectAddition());
                    break;

                case SourceSkillEffect::OP_SUB:
                    $value -= (integer)($o->o->getValue() * $o->h->getPassiveEffectAddition());
                    break;
                }
            }

            // auras
            foreach ($this->auraAccepted->get($prop, array(
                SourceSkillEffect::OP_ADD => [],
                SourceSkillEffect::OP_SUB => [],
            )) as $op => $o) {
                if ($o->count() == 0) {
                    continue;
                }

                $o = $o->head();
                switch ($op) {
                case SourceSkillEffect::OP_ADD:
                    $value += (integer)($o->o->getValue() * $o->h->getPassiveEffectAddition());
                    break;

                case SourceSkillEffect::OP_SUB:
                    $value -= (integer)($o->o->getValue() * $o->h->getPassiveEffectAddition());
                    break;
                }
            }

            $ret[$idx++] = $value;
        }

        if (count($ret) == 1) {
            return current($ret);
        }

        return $ret;
    }

    /**
     *
     * @return integer
     */
    private function getSpeedCorrection()
    {
        $correction = 0;

        switch ($this->plh->pe->a->type) {
        case SourceArmy::TYPE_CAVALRY:
            $correction = $this->plh->pos > 2 ? -50 : 0;
            break;

        case SourceArmy::TYPE_SPEARMAN:
        case SourceArmy::TYPE_INFANTRY:
            $correction = $this->plh->pos > 2 ? -20 : 0;
            break;

        case SourceArmy::TYPE_ARCHER:
            break;
        }

        return $correction;
    }

    /**
     * @return double
     */
    private function getPassiveEffectAddition()
    {
        return $this->passiveEffectAddition;
    }

    /**
     *
     * @return Object
     */
    private function triggerActiveSkill()
    {
        if ($this->atts->count() == 0) {
            return;
        }

        /**
         * 10007 int
         * 10008 att
         */
        $enhance = 100 + $this->plh->pe->enhance->int;
        $int     = array_sum($this->getPropValue([
                    10007 => 0,
                    10008 => 0,
                    ]));
        $prob = $enhance * $this->getOriProp('int') + $int;
		if ($this->bonds->{self::BOND_TRIGGER}) {
			$prob += $this->bonds->{self::BOND_TRIGGER}*300;
		}

        foreach ($this->atts as $o) {
            if (mt_rand(1, 30000) > $prob) {
                continue;
            }

            return $o;
        }
    }

    /**
     *
     * @param  Object $skill
     * @return array
     */
    private function triggerHitsEffects(Object $skill = null)
    {
        $effects = [];
        if ($skill) {
            // active-skill-effects
            foreach ($skill->s->effects as $se) {
                // 斩杀/固定伤害 不算机率
                if ($se->eid <> 10011 && $se->eid <> 10026 && mt_rand(1, 100) > $se->getValue()) {
                    continue;
                }

                $effects[$se->eid] = $se->getValue();
            }
        }

        // hits-effects
        foreach ($this->hits as $eid => $o) {
            $v = $o->head()->o->getValue();
            if (false === isset($effects[$eid]) || $effects[$eid] < $v) {
                $effects[$eid] = $v;
            }
        }

        return $effects;
    }

    /**
     *
     * @return mixed
     */
    private function triggerCriticalDamage()
    {
        /**
         * 10008 att
         * 10013 stm
         * 10019 stm%
         */
        $stm     = array_sum($this->getPropValue([
            10008 => 0,
            10013 => 0,
        ]));
        $prob = $this->getOriProp('stm') + $stm + $this->restraint ? 300 : 0;
        if ($this->pointsRaceBuff && $this->pointsRaceBuff == self::BUFF_SPEARMAN && $this->plh->pe->a->type == SourceArmy::TYPE_SPEARMAN) {
            $prob += 300;
        }

		if ($this->bonds->{self::BOND_CRIT}) {
			$prob += $this->bonds->{self::BOND_CRIT}*30;
		}

        if (mt_rand(1, 3000) > $prob) {
            return false;
        }

		$ratio = $this->bonds->{self::BOND_CRITRAT} ?: 0;
        return 200 + $this->getPropValue([
            10021 => $ratio,
        ]);
    }
}

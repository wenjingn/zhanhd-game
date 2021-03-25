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
use Zhanhd\Object\Player\Lineup as PlayerLineup,
    Zhanhd\ReqRes\CombatProcessInfo;

/**
 *
 */
class Module
{
    /**
     * @var Object
     */
    private $attackerCombatants = null,
            $defenderCombatants = null;

    /**
     * @var Object
     */
    private $alive = null,
            $auras = null;

    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->attackerCombatants = new Object;
        $this->defenderCombatants = new Object;

        $this->alive = new Object;
        $this->auras = new Object;
    }

    /**
     *
     * @param  PlayerLineup      $attacker
     * @param  PlayerLineup      $defender
     * @param  CombatProcessInfo $cpi
     * @return array
     */
    public function combat(PlayerLineup $attacker = null, PlayerLineup $defender = null, CombatProcessInfo $cpi, callable $init = null)
    {
        // formation-restraint
        $attackerRestraint = $defenderRestraint = false;
        if ($attacker && $defender && $attacker->f && $defender->f) {
            if (isset($attacker->f->restraints)) {
                foreach ($attacker->f->restraints as $rid => $yes) {
                    if ($rid == $defender->f->id) {
                        $attackerRestraint = true;
                        break;
                    }
                }
            }

            if (isset($defender->f->restraints)) {
                foreach ($defender->f->restraints as $rid => $yes) {
                    if ($rid == $attacker->f->id) {
                        $defenderRestraint = true;
                        break;
                    }
                }
            }
        }

        // setup attacker combatants
        if ($attacker) {
			$attackerBonds = $attacker->getBonds();
            foreach ($attacker->heros->filter(function($o) {
                return (boolean) $o->peid;
            }) as $plh) {
				$bonds = isset($attackerBonds[$plh->pos]) ? $attackerBonds[$plh->pos] : null;
                $combatant = new Combatant($plh, Combatant::CAMP_ATTACKER, $attacker->f, $attackerRestraint, $bonds);

                $this->alive->set($plh->peid, $combatant);
                $this->auras->set($plh->peid, $combatant->getAura());

                $this->attackerCombatants->set($plh->pos, $combatant);
            }
        }

        // setup defender combatants
        if ($defender) {
			$defenderBonds = $defender->getBonds();
            foreach ($defender->heros->filter(function($o) {
                return (boolean) $o->peid;
            }) as $plh) {
				$bonds = isset($defenderBonds[$plh->pos]) ? $defenderBonds[$plh->pos] : null;
                $combatant = new Combatant($plh, Combatant::CAMP_DEFENDER, $defender->f, $defenderRestraint, $bonds);

                $this->alive->set($plh->peid, $combatant);
                $this->auras->set($plh->peid, $combatant->getAura());

                $this->defenderCombatants->set($plh->pos, $combatant);
            }
        }

        if ($init) {
            $init($this->attackerCombatants, $this->defenderCombatants);
        }

        $cpi->attacker->resize($this->attackerCombatants->count());
        $cpi->defender->resize($this->defenderCombatants->count());

        // setup enemies, auras and in-queue
        $cq = new CombatQueue;
        foreach ($this->alive as $combatant) {
            /* setup aura-skills */
            foreach ($this->auras as $peid => $o) {
                $combatant->addAura($o, $this->alive->$peid);
            }

            // finalize combatant
            $combatant->finalize();

            // setup enemies
            switch ($combatant->getCamp()) {
            case Combatant::CAMP_ATTACKER:
                $combatant->setEnemies($this->defenderCombatants);
                break;
            case Combatant::CAMP_DEFENDER:
                $combatant->setEnemies($this->attackerCombatants);
                break;
            }

            // insert into combat-queue
            $cq->insert($combatant, $combatant->getCurSpeed());
        }

        /* setup response, effects actived after $combatant::finalize() */
        $i = $j = 0; foreach ($this->alive as $combatant) {
            switch ($combatant->getCamp()) {
            case Combatant::CAMP_ATTACKER:
                $cpi->attacker->get($i++)->fromCombatantObject($combatant);
                break;
            case Combatant::CAMP_DEFENDER:
                $cpi->defender->get($j++)->fromCombatantObject($combatant);
                break;
            }
        }

        // for CombatProcessInfo
        $sequence = new Object;
        if ($attacker === null || $defender === null) {
            $cpi->win->intval((int)($this->gameover() == 1));
            goto ret;
        }

        // start combat rounds
        while (($o = $cq->head())) {
            $retval = $o->combatant->attack();
            foreach ($retval->killed as $peid => $null) {
                $cq->remove($this->alive->$peid);
            }

            // combat-sequence
            $sequence->set(null, $retval->sequence);
            foreach ($cq->getSequence() as $combatant) {
                $retval->sequence->queue->set(null, $combatant);
            }

            // check hpoint
            if (($gameover = $this->gameover())) {
                break;
            }

            // re-priority combatants
            foreach ($retval->update as $peid => $null) {
                if ($peid == $o->combatant->plh->peid) {
                    $cq->remove($o->combatant);
                    $cq->insert($o->combatant, $o->combatant->getCurSpeed() + $o->priority);
                } else {
                    $combatant = $this->alive->$peid;
                    $priority  = $cq->remove($combatant);

                    $cq->insert($combatant, ceil(($priority - $o->priority) * $combatant->getCurSpeed() / $combatant->getRawSpeed() + $o->priority));
                }
            }
        }

        // setup cpi-win
        $cpi->win->intval((integer) ($gameover == 1));

ret:
        // setup cpi-sequence
        $cpi->sequence->resize($sequence->count());
        $damage = (new Object)->import([
            'attacker' => 0,
            'defender' => 0,
        ]);
        foreach ($sequence as $i => $o) {
            foreach ($o->involved as $detail) {
                if ($detail->combatant->getCamp() == Combatant::CAMP_ATTACKER) {
                    $damage->defender += $detail->damage;
                } else {
                    $damage->attacker += $detail->damage;
                }
            }
            $cpi->sequence->get($i)->fromObject($o);
        }

        return $damage;
    }

    /**
     *
     * @return integer
     */
    private function gameover()
    {
        if ($this->attackerCombatants->reduce(function($hp, $o) {
            return $hp + $o->getHpoint();
        }, 0) == 0) {
            return -1;
        }

        if ($this->defenderCombatants->reduce(function($hp, $o) {
            return $hp + $o->getHpoint();
        }, 0) == 0) {
            return 1;
        }

        return 0;
    }
}

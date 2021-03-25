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
class CombatQueue
{
    /**
     * @var Object
     */
    private $combatants = null,
            $idxhelpers = null;

    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->combatants = new Object;
        $this->idxhelpers = new Object;
    }

    /**
     *
     * @param  Combatant $combatant
     * @param  integer   $priority
     * @return void
     */
    public function insert(Combatant $combatant, $priority)
    {
        $this->combatants->get($priority, array())->set($combatant->getPriority(), $combatant)->sort(function($a, $b) {
            return $a - $b;
        }, 'uksort');

        $this->combatants->sort(function($a, $b) {
            return $a - $b;
        }, 'uksort');

        $this->idxhelpers->set($combatant->plh->peid, $priority);
    }

    /**
     *
     * @param  Combatant $combatant
     * @return mixed
     */
    public function remove(Combatant $combatant)
    {
        if (null === ($priority = $this->idxhelpers->get($combatant->plh->peid))) {
            return;
        }

        if ($this->combatants->get($priority)->count() == 1) {
            unset($this->combatants->$priority);
        } else {
            unset($this->combatants->$priority->{$combatant->getPriority()});
        }

        unset($this->idxhelpers->{$combatant->plh->peid});
        return $priority;
    }

    /**
     *
     * @return Combatant
     */
    public function head()
    {
        foreach ($this->combatants as $priority => $combatants) {
            foreach ($combatants as $combatant) {
                return (object) array(
                    'priority'  => $priority,
                    'combatant' => $combatant,
                );
            }

            return (object) array(
                'priority'   => $priority,
                'combatants' => $combatants,
            );
        }
    }

    /**
     *
     * @return Object
     */
    public function getSequence()
    {
        $objects = new Object;
        foreach ($this->combatants as $combatants) {
            foreach ($combatants as $combatant) {
                $objects->set($combatant->plh->peid, $combatant);
            }
        }

        return $objects;
    }

    /**
     *
     * @return void
     */
    public function debug()
    {
        foreach ($this->combatants as $priority => $combatants) {
            foreach ($combatants as $combatant) {
                $combatant->debug();
            }
        }
    }
}

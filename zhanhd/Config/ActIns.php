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
use System\Object\ConfigObject;

/**
 *
 */
use Zhanhd\Object\Player\Lineup;

/**
 *
 */
class ActIns extends ConfigObject
{
    /**
     * @struct self
     *
     * @id     integer
     * @npcnum integer
     * @armytp integer
     * @rarity integer
     * @floors [(integer)floor => ActInsFloor]
     */

    /**
     * @param integer $floor
     * @return array
     */
    public function getFloor($floor)
    {
        if (false === isset($this->floors) || false === isset($this->floors[$floor])) {
            return false;
        }

        return $this->floors[$floor];
    }

    /**
     * const integer
     */
    const NOERROR      = 0;
    const ERROR_NPCNUM = 1;
    const ERROR_ARMYTP = 2;
    const ERROR_RARITY = 3;

    /**
     * @param PlayerLineup $l
     * @return integer
     */
    public function check($l)
    {
        $count = 0;
        foreach ($l->heros as $h) {
            if ($h->peid == 0) {
                continue;
            }
            if ($this->armytp && $this->armytp != $h->pe->a->type) {
                return self::ERROR_ARMYTP;
            }

            if ($this->rarity && $this->rarity < $h->pe->e->rarity) {
                return self::ERROR_RARITY;
            }

            $count++;
        }

        if ($this->npcnum && $this->npcnum < $count) {
            return self::ERROR_NPCNUM;
        }

        return self::NOERROR;
    }

    /**
     * @return string
     */
    public function getCounterKey()
    {
        return sprintf('actins-%d', $this->id);
    }
}

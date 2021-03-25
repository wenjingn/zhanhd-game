<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Config\DayIns;

/**
 *
 */
use Zhanhd\Config\ActIns\Floor;

/**
 *
 */
class Diff extends Floor
{
    /**
     * @struct DayInsDiff
     *
     * @diff    integer
     * @rlvl    integer
     * @fmt     integer
     * @drop    [(integer)eid => (integer)num]
     * @npc     [(integer)pos => NPC]
     */

    /**
     * @struct DayInsNPC
     *
     * @eid integer
     * @lvl integer
     * @ehc integer
     */
}

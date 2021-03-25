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
class NPCLineup extends ConfigObject
{
    /**
     * @struct self
     * 
     * @id         integer
     * @fid        integer
     * @combatants array( (integer)pos => (stdClass) Combatant )
     */

    /**
     * @struct Combatant
     *
     * @pos integer
     * @eid integer
     * @lvl integer
     * @ehc integer
     */
}

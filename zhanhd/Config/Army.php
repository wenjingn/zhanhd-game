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
class Army extends ConfigObject
{
    /**
     * @struct self
     * 
     * @id           integer
     * @tag          string
     * @type         integer
     * @dex          integer
     * @dmg          integer
     * @dmgperlvl    integer
     * @def          integer
     * @defperlvl    integer
     * @hpt          integer
     * @hptperlvl    integer
     *
     * @upgradations array( (integer)eid => (integer)num )
     */

    /**
     * @const integer
     */
    const TYPE_CAVALRY  = 100; // 骑兵
    const TYPE_SPEARMAN = 200; // 枪兵
    const TYPE_INFANTRY = 300; // 步兵
    const TYPE_ARCHER   = 400; // 弓兵
}

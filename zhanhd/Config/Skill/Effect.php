<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Config\Skill;

/**
 *
 */
use System\Object\ConfigObject;

/**
 *
 */
class Effect extends ConfigObject
{
    /**
     * @struct self
     * 
     * @eid      integer
     * @at       integer
     * @op       integer
     * @to       integer
     * @anti     integer
     * @army     integer
     * @dynasty  integer
     * @diffrace integer
     * @v0       integer
     * @v1       integer
     * @v2       integer
     * @v3       integer
     * @v4       integer
     * @v5       integer
     */

    /**
     * @const integer
     */
    const AT_ALL   = 1;
    const AT_ONE   = 2;
    const OP_ADD   = 1;
    const OP_SUB   = 2;
    const TO_ALLY  = 1;
    const TO_ENEMY = 2;

    /**
     * @var integer
     */
    public $lvl = 1;

    /**
     * @return integer
     */
    public function getValue()
    {
        if ($this->v0) {
            return $this->v0;
        }

        switch ($this->lvl) {
        case 1: return $this->v1;
        case 2: return $this->v2;
        case 3: return $this->v3;
        case 4: return $this->v4;
        case 5: return $this->v5;
        }

        return 0;
    }
}

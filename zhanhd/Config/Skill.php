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
class Skill extends ConfigObject
{
    /**
     * @struct self
     *
     * @id      integer
     * @tag     string
     * @type    integer
     * @mode    string
     * @nums    integer
     * @once    boolean
     * @v1      integer
     * @v2      integer
     * @v3      integer
     * @v4      integer
     * @v5      integer
     * @effects array( (integer)eid => (SkillEffect)effect )
     */

    /**
     * @const integer
     */
    const TYPE_ACTIVE  = 1;
    const TYPE_PASSIVE = 2;
    const TYPE_INNATE  = 3;
    const TYPE_MATCHED = 4;

    /**
     * @const integer
     */
    const MAX_LEVEL = 5;
    const MIN_LEVEL = 1;

    /**
     * @var integer
     */
    public $lvl = 1;

    /**
     * @var array
     */
    public $effects = [];

    /**
     * @return void
     */
    protected function initial()
    {
        if (isset($this->object->effects)) {
            foreach ($this->object->effects as $k => $o) {
                $this->effects[$k] = new Skill\Effect($o);
            }
        }
    }

    /**
     * @param integer $lvl
     * @return void
     */
    public function setLevel($lvl)
    {
        $lvl = max(static::MIN_LEVEL, min($lvl, static::MAX_LEVEL));
        $this->lvl = $lvl;
        foreach ($this->effects as $o) {
            $o->lvl = $lvl;
        }
    }

    /**
     * @return integer
     */
    public function getValue()
    {
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

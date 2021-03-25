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
class Achievement extends ConfigObject
{
    /**
     * @struct self
     *
     * @id     integer
     * @tag    string
     * @type   integer
     * @cmd    string
     * @argv   string
     * @intval integer
     * @strval string
     * @unlock string
     *
     * @sources array( (integer)eid => (integer)num )
     */

    /**
     * @var const
     */
    const TYPE_MAIN   = 1;
    const TYPE_CYCLE  = 2;
    const TYPE_BRANCH = 3;

    /**
     *
     * @return string
     */
    public function getCounterKey()
    {
        switch ($this->type) {
        case self::TYPE_MAIN:
            return sprintf('%s%s', $this->cmd, $this->strval);

        case self::TYPE_BRANCH:
        case self::TYPE_CYCLE:
            return sprintf('%s%s%s', $this->cmd, $this->strval, $this->argv);
        }
    }

    /**
     * @return array
     */
    public function getRewards()
    {
        $rewards = [];
        foreach ($this->sources as $k => $v) {
            if (Store::has('entity', $k)) {
                $rewards[$k] = $v;
            } else if (Store::has('egroup', $k)) {
                $picked = Store::get('egroup', $k)->pick($v);
                foreach ($picked as $k => $v) {
                    if (false === isset($rewards[$k])) {
                        $rewards[$k] = $v;
                    } else {
                        $rewards[$k] += $v;
                    }
                }
            }
        }
        return $rewards;
    }
}

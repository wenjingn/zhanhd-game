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
class CompletionReward extends ConfigObject
{
    /**
     * @struct self
     * @integer idx
     * @integer completion
     * @reward array((int)k => (int)v)
     */

    /**
     * @return string
     */
    public function counterKey()
    {
        return sprintf('completionReward-%d', $this->type*100+$this->idx);
    }
}

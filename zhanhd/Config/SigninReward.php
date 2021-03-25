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
class SigninReward extends ConfigObject
{
    /**
     * @struct self
     *
     * @dom integer
     * @eid integer
     * @num integer
     * @vipnum integer
     */

    /**
     * @param boolean $isMember
     * @return array
     */
    public function getRewards($isMember)
    {
        return [
            $this->eid => $isMember ? $this->vipnum : $this->num,
        ];
    }
}

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
class Merchandise extends ConfigObject
{
    /**
     * @struct self
     *
     * @id  integer
     * @eid integer
     * @num integer
     * @price integer
     * @discountPrice integer
     */

    /**
     * @param integer $money
     * @return boolean
     */
    public function checkMoney($money)
    {
        return $this->price == $money;
    }

    /**
     * @param boolean isFirst
     * @return integer
     */
    public function getDiamond($isFirst)
    {
        if ($this->id == 101) {
            return 0;
        }

        return $isFirst ? $this->price*20 : $this->price*10 + $this->num;
    }

    /**
     * @return string
     */
    public function getCounterKey()
    {
        return sprintf('merchandise-%d', $this->id);
    }

    /**
     * @return string
     */
    public function getRechargeRewardKey()
    {
        return sprintf('recharge-r-%d', $this->id);
    }

    /**
     * @return string
     */
    public function getRechargeRewardAcceptedKey()
    {
        return sprintf('recharge-a-%d', $this->id);
    }
}

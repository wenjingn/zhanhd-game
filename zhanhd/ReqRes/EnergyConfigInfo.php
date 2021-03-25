<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16;

/**
 *
 */
class EnergyConfigInfo extends Box
{
    /**
     * @const integer
     */
    const INTERVAL = 60;
    const ENERGY = 1;
    const TASKNEED = 25;

    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('interval', new U16);
        $this->attach('energy',   new U16);
        $this->attach('taskNeed', new U16);

        $this->interval->intval(self::INTERVAL);
        $this->energy->intval(self::ENERGY);
        $this->taskNeed->intval(self::TASKNEED);
    }
}

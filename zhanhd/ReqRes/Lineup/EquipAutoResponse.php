<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Lineup;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set;

/**
 *
 */
class EquipAutoResponse extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(46);
        $this->attach('retval', new Set(new Equip));
    }
}

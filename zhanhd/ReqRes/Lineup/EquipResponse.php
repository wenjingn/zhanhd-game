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
class EquipResponse extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(26);
        $this->attach('retval', new Set(new Equip));
    }
}

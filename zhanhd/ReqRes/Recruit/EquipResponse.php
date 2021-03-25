<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Recruit;

/**
 *
 */
use System\Swoole\ReqResHeader;

/**
 *
 */
use Zhanhd\ReqRes\Entity\Equip;

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
        $this->command->intval(28);
        $this->attach('retval', new Equip);
    }
}

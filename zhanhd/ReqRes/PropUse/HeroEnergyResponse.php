<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\PropUse;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set;

/**
 *
 */
use Zhanhd\ReqRes\HeroEnergyInfo;

/**
 *
 */
class HeroEnergyResponse extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(86);
        $this->attach('heros', new Set(new HeroEnergyInfo));
    }
}

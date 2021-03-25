<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Building\Upgrade;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set;

/**
 *
 */
use Zhanhd\ReqRes\Building\Building;

/**
 *
 */
class Response extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(34);

        $this->attach('buildings', new Set(new Building));
    }
}

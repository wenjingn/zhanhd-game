<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\PointsRace\Tarlist;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set;

/**
 *
 */
use Zhanhd\ReqRes\PointsRace\Target;

/**
 *
 */
class Response extends ReqResHeader
{
    /**
     * return void
     */
    protected function setupResponse()
    {
        $this->command->intval(257);
        $this->attach('targets', new Set(new Target));
    }
}

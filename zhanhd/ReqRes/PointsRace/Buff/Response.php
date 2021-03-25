<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\PointsRace\Buff;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U08;

/**
 *
 */
class Response extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(261);
        $this->attach('buff', new U08);
    }
}

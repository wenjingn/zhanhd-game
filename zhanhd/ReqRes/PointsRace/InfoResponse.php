<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\PointsRace;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U08,
    System\ReqRes\Int\U16;

/**
 *
 */
class InfoResponse extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(255);
        $this->attach('buff', new U08);
        $this->attach('cwin', new U16);
        $this->attach('score', new U16);
        $this->attach('rank', new U16);

        $this->attach('conswin', new U08);
        $this->attach('challenged', new U08);
        $this->attach('refreshed', new U08);
    }
}

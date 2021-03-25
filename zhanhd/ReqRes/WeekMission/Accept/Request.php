<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\WeekMission\Accept;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16;

/**
 *
 */
class Request extends Box
{
    /**
     * @return void
     */
    public function initial()
    {
        $this->attach('mid', new U16);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\NewzoneMission\Accept;

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
    protected function initial()
    {
        $this->attach('day', new U16);
        $this->attach('idx', new U16);
    }
}

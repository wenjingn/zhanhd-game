<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Rob\Revenge;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U64;

/**
 *
 */
class Request extends Box
{
    /**
     * @eeturn void
     */
    protected function initial()
    {
        $this->attach('logid', new U64);
    }
}

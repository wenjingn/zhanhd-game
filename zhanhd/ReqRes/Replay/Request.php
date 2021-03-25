<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Replay;

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
     * @return void
     */
    protected function initial()
    {
        $this->attach('replayId', new U64);
    }
}

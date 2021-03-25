<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Hero\Refine;

/**
 *
 */
use System\Swoole\ReqResHeader;

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
        $this->command->intval(151);
        $this->attach('refine', new Info);
    }
}

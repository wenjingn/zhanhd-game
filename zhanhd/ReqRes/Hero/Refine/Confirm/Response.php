<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Hero\Refine\Confirm;

/**
 *
 */
use System\Swoole\ReqResHeader;

/**
 *
 */
use Zhanhd\ReqRes\Hero\Refine\Info;

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
        $this->command->intval(153);
        $this->attach('refine', new Info);
    }
}

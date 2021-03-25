<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild\Pending\Approve;

/**
 *
 */
use System\Swoole\ReqResHeader;

/**
 *
 */
use Zhanhd\ReqRes\Guild\Info;

/**
 *
 */
class Notify extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(195);
        $this->attach('guild', new Info);
    }
}

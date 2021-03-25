<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild\Manage\Bulletin;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Str;

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
        $this->command->intval(191);
        $this->attach('bulletin', new Str);
    }
}

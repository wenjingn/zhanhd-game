<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild\Manage\Appoint;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Str,
    System\ReqRes\Int\U64;

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
        $this->command->intval(204);
        $this->attach('pid', new U64);
        $this->attach('viceChairmanName', new Str);
    }
}

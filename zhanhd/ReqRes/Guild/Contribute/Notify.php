<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild\Contribute;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Str,
    System\ReqRes\Int\U08;

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
        $this->command->intval(272);
        $this->attach('nickname', new Str);
        $this->attach('contId', new U08);
    }
}

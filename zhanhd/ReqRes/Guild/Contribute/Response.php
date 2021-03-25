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
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U32;

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
        $this->command->intval(214);
        $this->attach('cid',          new U16);
        $this->attach('ignore',       new U16);
        $this->attach('contribution', new U32);
    }
}

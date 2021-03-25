<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Fragment;

/**
 *
 */
use System\Swoole\ReqResHeader,
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
        $this->command->intval(187);
        $this->attach('frag', new U32);
        $this->attach('cost', new U32);
        $this->attach('eid',  new U32);
        $this->attach('incr', new U32);
    }
}

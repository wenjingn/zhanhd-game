<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Crusade\Recall;

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
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(24);

        $this->attach('cid', new U32);
        $this->attach('gid', new U32);
    }
}

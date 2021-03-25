<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild\Member\Page;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set,
    System\ReqRes\Int\U16;

/**
 *
 */
use Zhanhd\ReqRes\Guild\MemberInfo;

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
        $this->command->intval(200);
        $this->attach('index',   new U16);
        $this->attach('total',   new U16);
        $this->attach('members', new Set(new MemberInfo));
    }
}

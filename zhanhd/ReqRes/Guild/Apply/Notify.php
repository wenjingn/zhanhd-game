<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guild\Apply;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set;

/**
 *
 */
use Zhanhd\ReqRes\Guild\MemberInfo;

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
        $this->command->intval(196);
        $this->attach('applies', new Set(new MemberInfo));
    }
}

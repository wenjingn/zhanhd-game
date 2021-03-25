<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\MessageMail\Page;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set,
    System\ReqRes\Int\U16,
    System\ReqRes\Int\U64;

/**
 *
 */
use Zhanhd\ReqRes\MessageMailInfo;

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
        $this->command->intval(112);
        $this->attach('page',  new U16);
        $this->attach('mails', new Set(new MessageMailInfo));
    }
}

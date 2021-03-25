<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Invite\Reward;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U16;

/**
 *
 */
class Request extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('irid', new U16);
    }
}

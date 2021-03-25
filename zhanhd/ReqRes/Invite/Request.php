<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Invite;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Str;

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
        $this->attach('invcode', new Str);
    }
}

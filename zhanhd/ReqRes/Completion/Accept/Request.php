<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Completion\Accept;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U08;

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
        $this->attach('type', new U08);
        $this->attach('idx', new U08);
    }
}

<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Completion\Poll;

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
     * @const integer
     */
    const TYPE_TASK = 1;
    const TYPE_ROB = 2;

    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('type', new U08);
    }
}

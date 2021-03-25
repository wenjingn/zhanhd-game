<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\QA;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32,
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
        $this->attach('version', new U32);
        $this->attach('qid',    new U16);
        $this->attach('answer', new U16);
    }
}

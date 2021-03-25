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
use System\ReqRes\Box,
    System\ReqRes\Int\U32;

/**
 *
 */
class Request extends Box
{
    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('cid', new U32);
    }
}

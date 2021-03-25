<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Guide;

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
     * @const integer
     */
    const MAX_STEP = 14;

    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('guideId', new U16);
    }
}

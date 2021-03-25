<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U32;

/**
 *
 */
class ActivityMetaInfo extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('aid',    new U32);
        $this->attach('remain', new U32);
    }
}

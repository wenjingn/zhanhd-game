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
class FormationInfo extends Box
{
    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('gid', new U32);
        $this->attach('fid', new U32);
    }
}

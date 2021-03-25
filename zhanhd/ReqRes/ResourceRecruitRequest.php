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
class ResourceRecruitRequest extends Box
{
    /**
     *
     * @return void
     */
    protected function initial()
    {
        $this->attach('soldier', new U32);
        $this->attach('weapon',  new U32);
        $this->attach('armor',   new U32);
        $this->attach('horse',   new U32);
    }
}

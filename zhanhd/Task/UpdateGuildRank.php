<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\Task;

/**
 *
 */
use System\ReqRes\Box,
    System\ReqRes\Int\U64;

/**
 *
 */
class UpdateGuildRank extends Box
{
    /**
     * @return void
     */
    protected function initial()
    {
        $this->attach('gid', new U64);
    }
}

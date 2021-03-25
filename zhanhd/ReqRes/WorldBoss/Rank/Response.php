<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\WorldBoss\Rank;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Set;

/**
 *
 */
class Response extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(181);
        $this->attach('ranklist', new Set(new Info));
    }
}

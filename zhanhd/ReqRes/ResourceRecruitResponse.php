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
use System\Swoole\ReqResHeader,
    System\ReqRes\Set;

/**
 *
 */
use Zhanhd\ReqRes\Entity\Hero;

/**
 *
 */
class ResourceRecruitResponse extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(30);
        $this->attach('retval', new Set(new Hero));
    }
}

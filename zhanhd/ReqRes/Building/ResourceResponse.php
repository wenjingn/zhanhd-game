<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Building;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

/**
 *
 */
use Zhanhd\ReqRes\ResourceInfo;

/**
 *
 */
class ResourceResponse extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(11);
        $this->attach('causeId', new U32);
        $this->attach('retval',  new ResourceInfo);
    }
}

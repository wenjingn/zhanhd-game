<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\PropUse;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U32;

/**
 *
 */
class PropRemainResponse extends ReqResHeader
{
    /**
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(108);
        $this->attach('propId', new U32);
        $this->attach('num',    new U32);
    }
}

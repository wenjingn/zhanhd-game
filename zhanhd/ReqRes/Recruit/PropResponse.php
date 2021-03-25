<?php
/**
 * $Id$
 */

/**
 *
 */
namespace Zhanhd\ReqRes\Recruit;

/**
 *
 */
use System\Swoole\ReqResHeader,
    System\ReqRes\Int\U16;

/**
 *
 */
use Zhanhd\ReqRes\Entity\Prop;

/**
 *
 */
class PropResponse extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(78);
        $this->attach('retval', new Prop);
        $this->attach('times',  new U16);
    }
}

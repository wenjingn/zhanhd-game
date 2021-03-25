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
    System\ReqRes\Int\U16;

/**
 *
 */
use Zhanhd\ReqRes\Entity\Prop;

/**
 *
 */
class FixedTimePropResponse extends ReqResHeader
{
    /**
     *
     * @return void
     */
    protected function setupResponse()
    {
        $this->command->intval(222);
        $this->attach('id', new U16);
        $this->attach('prop', new Prop);
    }
}
